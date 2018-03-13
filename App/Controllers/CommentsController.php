<?php
namespace App\Controllers;

use App\Models\CommentsModel;
use \App\Controllers\Controller;
use \DateTime;
use App\System\App;
use DOMDocument;

class CommentsController extends Controller {
    
    protected $table = "comments";

    public function add() {
        if(!empty($_POST)){
                $text  = isset($_POST['comment']) ? $_POST['comment'] : '';
                $model = new CommentsModel;
                $model->create([
                    'created_at' => date('Y-m-d H:i:s'),
                    'user'       => $_COOKIE['user'],
                    'text'       => $this->sanitizeText($text)
                ]);
            }
         App::redirect('dashboard');
       }

    public function sanitizeText($text) {
        $dom = new DOMDocument;
        @$dom->loadHTML($text);
        $links = $dom->getElementsByTagName('a');
        $comment = '';
        foreach ($dom->childNodes as $element) {
            $comment = $comment . $element->nodeValue;
        }
        foreach ($links as $link){
            $href = $link->getAttribute('href');
            if (filter_var($href, FILTER_VALIDATE_URL)) {
                $text = $link->nodeValue;
                $pos = strpos($comment, $text);
                if ($pos > -1) {
                    $comment = str_replace($text, '<a href="' .
                        $href . '" onClick="return confirm(\'You are now navigating away from the site. Are you sure you want to do that?\')">' .
                        $text . '</a>', $comment);
                }
            }
        }
        return $comment;
    }
}