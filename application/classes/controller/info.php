<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Info extends Controller_Template {
    public $template = 'base/template';
    public $user = null;

    /**
     * Index action
     */
    public function action_index() {
        // get the content
        $view = $this->template->content = View::factory('info/index');
        $this->template->okjs = true;
    }

    public function before() {
        parent::before();
        $this->user = Auth::instance()->get_user();
        $this->template->user = $this->user;
        $this->template->model = 'info';
        $this->template->action = Request::instance()->action;
   }
}
