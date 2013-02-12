<?php
/**
 * Simple model for a book.
 * User: xAlf Magne Kalleland
 * Date: 03.02.13

 */
class Book extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true; // Config on JSONConfig/Book.json

    public function validateArguments($service, $arguments){
        switch($service){
            case 'delete':
                return count($arguments) === 1 && is_numeric($arguments[0]);
            default:
                return count($arguments) === 0 || is_numeric($arguments[0]) && count($arguments) === 1;
        }
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function getValidServices(){
        return array('read','save','delete');
    }

    public function save($data){
        $ret = parent::save($data);
        if(isset($data['author'])){
            $id = $this->getId();
            $authors = explode(";", $data['author']);
            foreach($authors as $author){
                $a = new Author();
                $a->setName($author);
                $a->commit();

                $bookAuthor = new BookAuthor();
                $bookAuthor->setBookId($id);
                $bookAuthor->setAuthorId($a->getId());
                $bookAuthor->commit();
            }
        }
        return $ret;
    }
}
