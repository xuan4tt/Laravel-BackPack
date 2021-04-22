<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class TestController extends Controller
{   
    
    public function test(){
        $client = new Client();
        $crawler = $client->request('GET', 'https://khoahoc.vietjack.com/thi-online/30-de-thi-thu-thpt-quoc-gia-mon-vat-li-2020-cuc-hay-noi-loi-giai-chi-tiet/24275');
        $crawler->filter('div.qas > div.quiz-answer-item')->each( 
            function(Crawler $node) {
                $question  =  $node->filter('a.question')->html();
                //printf($array);
                $node->filter('div > label')->each(function(Crawler $node2){
                    $answer = $node2->filter('p')->html();
                     //echo $answer;
                });
                $node->filter('div > div.reason')->each(function(Crawler $node3){
                    $correct_answer = $node3->filter('p')->html();
                    //echo $correct_answer;
                });
            }
            
        );

        

        // $test = $crawler->filter('script')->html();
        // echo $test;
    }
}
