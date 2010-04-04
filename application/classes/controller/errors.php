<?php
class Controller_Errors extends Controller_Template {
    public $template = 'base/template';

    public function action_404() {
        $this->request->status = 404;
        $this->template->content = View::factory('errors/404');
        $this->template->content->user = Auth::instance()->get_user();
        $this->template->okjs = true;
        $this->template->title = '404 Not Found';
    }

    public function before() {
        parent::before();
        $this->template->content = '';
        $this->template->model = 'errors';
        $this->template->action = Request::instance()->action;
   }
}