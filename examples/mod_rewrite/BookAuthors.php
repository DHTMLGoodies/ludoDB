<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 03.02.13

 */
class BookAuthors extends LudoDBCollection
{
    protected $config = array(
        "sql" => "select author.name from author, book_author where book_author.author_id = author.id and book_author.book_id=? order by author.name"
    );

    public function getValues(){
        return implode(", ", $this->getColumnValues('name'));
    }
}
