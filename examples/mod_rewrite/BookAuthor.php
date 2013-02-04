<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 03.02.13
 * Time: 16:26
 */
class BookAuthor extends LudoDBModel
{
    protected $JSONConfig = true;

    public function setBookId($id){
        $this->setValue('book_id', $id);
    }

    public function setAuthorId($id){
        $this->setValue('author_id', $id);
    }
}
