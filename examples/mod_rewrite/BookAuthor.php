<?php
/**
 * Book-Author relationship table
 * User: Alf Magne
 * Date: 03.02.13

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
