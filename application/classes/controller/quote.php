<?php
class Controller_Quote extends Controller_Template {
    public $template = 'base/template';

    /**
     * Top action corresponds to /quote/top
     * 
     * Top rated quotes
     */
    public function action_top() {
        // count items
        $count = DB::select(DB::expr('COUNT(quote_id) AS count'))->from('voteaverages')->execute('default')->get('count');

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p'),
            'total_items'    => $count,
            'items_per_page' => QUOTES_ITEMS_PER_PAGE,
        ));

        // get the content
        $view = $this->template->content = View::factory('quotes/quotes');
        // top rated quotes
        $top_voteaverages = ORM::factory('voteaverage')
            ->order_by('average','desc')
            ->order_by('quote_id','desc')
            ->limit($pagination->items_per_page)
            ->offset($pagination->offset)
            ->find_all()
        ;

        $view->quotes = array();
        foreach ($top_voteaverages as $voteaverage) {
            $view->quotes[] = $voteaverage->quote;
        }

        // render the pager
        $view->pager = $pagination->render();
    }


    /**
     * Edit action corresponds to /quote/edit
     */
    public function action_delete() {
        if (!$this->template->user) {
            //Request::instance()->redirect('user/login');
        }

        $id = $this->request->param('id');
        $quote = new Model_Quote($id);

        $this->template->content = $view = new View('quotes/delete');
        $view->error = 0;

        if (!$quote->loaded()) {
            $view->error = 1;
            return ;
        }

        $quote->delete($id);

        $this->template->title = 'Deleted quote ' . $quote->id;
    }

    /**
     * Edit action corresponds to /quote/edit
     */
    public function action_edit() {
        if (!$this->template->user) {
            Request::instance()->redirect('user/login');
        }

        $this->template->content = $view = new View('quotes/add');
        $view->action = 'edit';

        $view->id = $id = $this->request->param('id');
        $quote = new Model_Quote($id);


        if (!$quote->loaded()) {
            return ;
        }
        $view->quote = $quote;

        $this->template->title = 'Editing quote ' . $quote->id;

        $view->text = $quote->text;
        $view->author = $quote->author->name;
        $view->categories = '';
        foreach ($quote->categories_list as $category) {
            $view->categories = $category->name . ', ';
        }
        $view->categories = substr($view->categories, 0, -2);

        if ($_POST) {
            $view->error = 0;
            // validate data first
            $post = new Validate($_POST);
            $post
                ->rule('text', 'min_length', array(5))
                ->rule('categories', 'max_length', array(2000))
                ->rule('author', 'min_length', array(5))
                ->rule('author', 'max_length', array(500))
                ->filter(TRUE, 'trim')
            ;

            if ($post->check()) {
                // check author exists
                $author = ORM::factory('author')->where('name', '=', $post['author'])->find();
                $valid_author_id = $author->id;
                if (!$author->loaded()) {
                    // create author if does not exist
                    $author_controller = new Controller_Author($this->request);
                    $valid_author_id = $author_controller->_add(array(
                        'author_name' => $post['author']
                    ));
                }

                // create quote
                $quote = new Model_Quote($id);
                $quote->text = $post['text'];
                $quote->author_id = $valid_author_id;

                if ($quote->save()) {
                    if ($post['categories']) {
                        // use categories as csv
                        $categories_string = explode(',', $post['categories']);
                        foreach ($categories_string as $i => $category) {
                            $categories_string[$i] = $category = trim($category);
                        }
                        $categories_string = array_unique($categories_string);

                        $category_controller = new Controller_Category($this->request);
                        foreach($categories_string as $category) {
                            $cat = ORM::factory('category')->where('name', '=', $category)->find();
                            if ($quote->has('categories', $cat)) continue;
                            if ($cat->loaded()) {
                                $quote->add('categories', $cat);
                                unset($categories_string[$i]);
                            } else {
                                $cat = $category_controller->_add(array(
                                    'category_name' => $category
                                ));
                                if ($cat) {
                                    $quote->add('categories', $cat);
                                }
                            }
                        }
                    }

                    $view->text = $quote->text;
                    $view->author = $quote->author->name;
                    $view->categories = '';
                    foreach ($quote->categories_list as $category) {
                        $view->categories = $category->name . ', ';
                    }
                    $view->categories = substr($view->categories, 0, -2);
                    // success!
                    $this->template->title = 'Quote updated';
                } else {
                    // failure
                    $view->error = 1;
                    $this->template->title = 'Error saving quote';
                }
            }
            else {
                $view->error = 1;
                $this->template->title = 'Error saving quote';
            }
        } else {
            $this->template->title = 'Edit quote ' . $id;
        }
    }

    /**
     * Add action corresponds to /quote/add
     * 
     * Adds quotes and automatically creates authors if they do not exist
     */
    public function action_add() {
        if (!$this->template->user) {
            Request::instance()->redirect('user/login');
        }
        $this->template->content = $view = new View('quotes/add');
        $view->action = 'add';

        $view->text = '';
        $view->author = '';
        $view->categories = '';

        if ($_POST) {
            $view->error = 0;
            // validate data first
            $post = new Validate($_POST);
            $post
                ->rule('text', 'min_length', array(5))
                ->rule('categories', 'max_length', array(2000))
                ->rule('author', 'min_length', array(5))
                ->rule('author', 'max_length', array(500))
                ->filter(TRUE, 'trim')
            ;

            if ($post->check()) {
                // check author exists
                $author = ORM::factory('author')->where('name', '=', $post['author'])->find();
                $valid_author_id = $author->id;
                if (!$author->loaded()) {
                    // create author if does not exist
                    $author_controller = new Controller_Author($this->request);
                    $valid_author_id = $author_controller->_add(array(
                        'author_name' => $post['author']
                    ));
                }

                // create quote
                $quote = new Model_Quote;
                $quote->text = $post['text'];
                $quote->author_id = $valid_author_id;

                if ($quote->save()) {
                    if ($post['categories']) {
                        // use categories as csv
                        $categories_string = explode(',', $post['categories']);
                        foreach ($categories_string as $i => $category) {
                            $categories_string[$i] = $category = trim($category);
                        }
                        $categories_string = array_unique($categories_string);

                        $category_controller = new Controller_Category($this->request);
                        foreach($categories_string as $category) {
                            $cat = ORM::factory('category')->where('name', '=', $category)->find();
                            if ($cat->loaded()) {
                                $quote->add('categories', $cat);
                                unset($categories_string[$i]);
                            } else {
                                $cat = $category_controller->_add(array(
                                    'category_name' => $category
                                ));
                                if ($cat) {
                                    $quote->add('categories', $cat);
                                }
                            }
                        }
                    }

                    $view->quote = $quote;
                    // success!
                    $this->template->title = 'Quote added';
                } else {
                    // failure
                    $view->error = 1;
                    $this->template->title = 'Error saving quote';
                    $view->text = $_POST['text'];
                    $view->author = $_POST['author'];
                    $view->categories = $_POST['categories'];
                }
            }
            else {
                $view->error = 1;
                $this->template->title = 'Error saving quote';
                $view->text = $_POST['text'];
                $view->author = $_POST['author'];
                $view->categories = $_POST['categories'];
            }
        } else {
            $this->template->title = 'Add quotes';
        }
    }

    /**
     * Index action, lists quotes
     * 
     * Ordered descendingly by ID.
     * Also handles pagination.
     */
    public function action_index() {
        // count items
        $count = DB::select(DB::expr('COUNT(id) AS count'))->from('quotes')->execute('default')->get('count');

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p'),
            'total_items'    => $count,
            'items_per_page' => QUOTES_ITEMS_PER_PAGE,
        ));

        // get the content
        $view = $this->template->content = View::factory('quotes/quotes');
        $view->quotes = ORM::factory('quote')->order_by('id','desc')
             ->limit($pagination->items_per_page)
             ->offset($pagination->offset)
             ->find_all()
        ;

        // render the pager
        $view->pager = $pagination->render();
    }


    /**
     * Shows the author page, lists some quotes
     */
    public function action_id() {
        $id = $this->request->param('id');
        $quote = new Model_Quote($id);

        $view = $this->template->content = View::factory('quotes/quote');

        if (!$quote->loaded()) {
            return ;
        }

        $view->quote = $quote;

        $view->quotes = array();
        $count = 0;
        foreach($quote->categories_list as $category) {
            if ($count > QUOTES_ITEMS_PER_PAGE) break;
            $quotes = $category->quotes->find_all();
            foreach ($quotes as $q) {
                if ($id == $q->id) continue;
                if ($count > QUOTES_ITEMS_PER_PAGE) break;
                $view->quotes[] = $q;
                $count++;
            }
        }
        $view->count = $count;

        $this->template->title = 'Quote ' . $quote->id;
    }

    public function action_feed() {
        $info = array(
              'title' => 'Newest Quotes - Quoteden',
              'pubDate' => date("D, d M Y H:i:s T"),
              'description' => 'Newest quotes from Quoteden',
          ); 

        $quotes = ORM::factory('quote')->order_by('id','desc')
             ->limit(QUOTES_ITEMS_PER_PAGE)
             ->offset($pagination->offset)
             ->find_all()
        ;

        $items = array();
        foreach ($quotes as $quote) {
            $items[] = array(
                'title' => $quote->id,
                'link' => 'quote/id/' . $quote->id,
                'description' => $quote->text
                    . '<br/><br/>'
                    . '<a href =" ' . Url::site('author/id/' . $quote->author->id) . '" title="More quotes by this author">'
                    . $quote->author->name . '</a>'
                    ,
            );
        }
        print $xml = Feed::create($info, $items);
    }

    public function before() {
        parent::before();
        $this->template->user = Auth::instance()->get_user();
        $this->template->model = 'quote';
        $this->template->action = Request::instance()->action;
   }
}
