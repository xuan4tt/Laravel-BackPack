<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('post') }}'><i class='nav-icon la la-minus-square'></i>
        Posts</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon la la-question'></i>
        Categories</a>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('crawling') }}'><i class='nav-icon la la-question'></i> Crawlings</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('question') }}'><i class='nav-icon la la-question'></i> Questions</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('answer') }}'><i class='nav-icon la la-question'></i> Answers</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('correct_answer') }}'><i class='nav-icon la la-question'></i> Correct_answers</a></li>