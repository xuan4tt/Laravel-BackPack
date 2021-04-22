<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Auth;
use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

/**
 * Class PostCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PostCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Post::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/post');
        CRUD::setEntityNameStrings('post', 'posts');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // $this->crud->enableDetailsRow();
        CRUD::setFromDb(); // columns
        CRUD::addColumn([
            'name' => 'AutherID',
            'type' => 'relationship',
            'label' => 'User',
            'entity' => 'user',
            'attribute' => 'name',
            'model' => User::class
        ]);

        CRUD::addColumn([
            'name' => 'category',
            'type' => 'relationship',
            'label' => 'Category',
            'entity' => 'category',
            'attribute' => 'name',
            'model' => Category::class
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'Date time',
        ]);

        CRUD::column('autherID')->remove();
        CRUD::column('content')->remove();
        CRUD::column('categoryID')->remove();


        CRUD::addFilter(
            [
                'type'  => 'simple',
                'name'  => 'active',
                'label' => 'Active'
            ],
            false,
            function () { // if the filter is active
                // $this->crud->addClause('active'); // apply the "active" eloquent scope 
            }
        );

        CRUD::addFilter(
            [
            'type' => 'date',
            'name' => 'Date',
            'lable' => 'Date'
            ],
            false,
            function ($value) {
            CRUD::addClause('where', 'created_at', 'LIKE' ,"%$value%");
            }
        );

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

        CRUD::setValidation(PostRequest::class);

        CRUD::addField([
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
        ]);

        CRUD::addField([
            'label' => 'Category',
            'type' => 'select',
            'name' => 'categoryID',
            'entity' => 'category',
            'attribute' => 'name',
            'model' => Category::class,
        ]);

        CRUD::addField([
            'label' => 'AutherID',
            'type' => 'hidden',
            'name' => 'autherID',
            'value' => Auth::guard(backpack_guard_name())->user()->id
        ]);

        CRUD::addField(
            [   // CKEditor
                'name'          => 'content',
                'label'         => 'Content',
                'type'          => 'ckeditor',
            ],
        );
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

    // public function showDetailsRow($id)
    // {
    //     dd(Post::find($id)->category);
    // }

    protected function setupShowOperation(){
        $this->crud->set('show.setFromDb', false);
    }



}
