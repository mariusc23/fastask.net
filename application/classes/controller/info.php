<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Info extends Controller_Template {
    public $template = 'base/template';

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
        $this->request->headers['Content-Type'] = 'text/html; charset=' .
                                                  Kohana::$charset;
        $this->template->model = 'info';
        $this->template->action = Request::instance()->action;
   }
}
