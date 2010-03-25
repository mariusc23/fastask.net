<h1>Add Quote</h1>
<?php
$submit = 'quote/add';
$attr = array('class'=>'quote_form');
$hidden = array();

if (isset($data)) {
    $text = $data['quote_text'];
    $author = $data['quote_author'];
    if ($error) {
        print 'Error occured.';
        print '<br/>';
    }
} else {
    $text = '';
    $author = '';
}
print form::open($submit, $attr, $hidden);

print form::label('quote_text', 'Text:');
print '<br/>';
print form::textarea('quote_text', $text);

print '<br/>';
print '<br/>';

print form::label('quote_author', 'Author:');
print '<br/>';
print form::input('quote_author', $author);

print '<br/>';
print '<br/>';

print form::submit('submit', 'Add quote');

print form::close();

echo '<a href="' . Url::site('quote/index') . '">Quotes list</a>';