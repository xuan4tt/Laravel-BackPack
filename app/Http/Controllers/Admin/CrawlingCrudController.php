<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CrawlingRequest;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Correct_answer;
use App\Models\Crawling;
use App\Models\Question;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Support\Facades\Auth;
use Prologue\Alerts\Facades\Alert;

//Crawl
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CrawlingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CrawlingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Crawling::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/crawling');
        CRUD::setEntityNameStrings('crawling', 'crawlings');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // columns
        
        CRUD::addColumn([
            'label' => 'Category',
            'type' => 'relationship',
            'name' => 'category',
            'entity' => 'CrawlingCategory',
            'attribute' => 'name',
            'model' => Category::class
        ]);
        
        CRUD::addColumn([
            'label' => 'User',
            'type' => 'relationship',
            'name' => 'user',
            'entity' => 'CrawlingUser',
            'attribute' => 'name',
            'model' => User::class
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'Date time',
        ]);
        
        CRUD::column('category_id')->remove();
        CRUD::column('user_id')->remove();
        /** 
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        //CRUD::setValidation(CrawlingRequest::class);
        //CRUD::setFromDb(); // fields
        CRUD::addField([
            'label' => 'Category',
            'type' => 'select',
            'name' => 'category_id',
            'entity' => 'CrawlingCategory',
            'attribute' => 'name',
            'model' => Category::class
        ]);
        CRUD::addField([
            'label' => 'Url Crawl',
            'type' => 'text',
            'name' => 'url'
        ]);

        CRUD::addField([
            'label' => 'AutherID',
            'type' => 'hidden',
            'name' => 'user_id',
            'value' => Auth::guard(backpack_guard_name())->user()->id
        ]);

        // CRUD::addField(

        // );
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function store(CrawlingRequest $CrawlingRequest)
    {
        //Alert::add('light', 'You have successfully <b>Crawl</b>')->flash();
        $request = $CrawlingRequest->all();
        $url = $request['url'];
        $client = new Client();
        try {
            if (Crawling::where('url', $url)->first()) {
                Alert::add('error', 'Url đã được phân tích !')->flash();
                return redirect()->back();
            }

            $crawler = $client->request('GET', $url);
            if ($crawler->filter('div.qas > div.quiz-answer-item')->count() == 0) {
                Alert::add('error', 'Trang web này không thể phân tích')->flash();
                return redirect()->back();
            }
            
            // Create data to table Crawling
            $Crawling = new Crawling;
            $Crawling->category_id = $request['category_id'];
            $Crawling->user_id = $request['user_id'];
            $Crawling->url = $request['url'];
            $Crawling->save();
            

            $crawler->filter('div.qas > div.quiz-answer-item')->each(
                function (Crawler $node) {
                    $CrawlingID =  Crawling::orderByDesc('id')->first()->id;
                    $QuestionCrawler = $node->filter('a.question')->html();

                    // Create data to table Question
                    $Question = new Question;
                    $Question->crawling_id = $CrawlingID;
                    $Question->content = $QuestionCrawler;
                    $Question->save();

                    $node->filter('div > label')->each(function (Crawler $node2) {
                        $QuestionID = Question::orderByDesc('id')->first()->id;   
                        $AnswerCrawler = $node2->filter('p')->html();

                        // Create data to table Answer
                        $Answer = new Answer;
                        $Answer->question_id = $QuestionID;
                        $Answer->content = $AnswerCrawler;
                        $Answer->save();
                        
                    });

                    $node->filter('div > div.reason')->each(function (Crawler $node3) {
                        $QuestionID = Question::orderByDesc('id')->first()->id;
                        $correct_answer = $node3->filter('p')->html();

                        // Create data to table Correct_answer
                        $CorectAnswer = new Correct_answer;
                        $CorectAnswer->question_id =  $QuestionID;
                        $CorectAnswer->content = $correct_answer;
                        $CorectAnswer->save();
                    });
                }
            );

            Alert::add('success', 'success')->flash();
            return redirect()->back();

        } catch (Exception $e) {
            Alert::add('error', 'Url could not be parsed !')->flash();
            return redirect()->back();
        }

        //return redirect()->back();
    }

    
}
