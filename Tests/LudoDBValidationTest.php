<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 17.04.13
 * Time: 21:07
 */

require_once(__DIR__ . "/../autoload.php");


class LudoDBValidationTest extends TestBase
{


    public function setUp()
    {
        parent::setUp();
        $util = new LudoDBUtility();
        $util->dropAndCreate(array('PersonWithValidation'));
    }

    /**
     * @test
     */
    public function shouldFindColumnsToValidate()
    {
        // given
        $model = new PersonWithValidation();

        // when
        $columnToValidate = $model->configParser()->getColumnsToValidate();

        // then
        $this->assertArrayHasKey("firstname", $columnToValidate);
        $this->assertArrayHasKey("mail", $columnToValidate);
        $this->assertArrayHasKey("nameWithMinLength", $columnToValidate);
        $this->assertArrayNotHasKey("id", $columnToValidate);
    }


    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenTryingToSaveWhenDefaultValueIsMissing()
    {

        $person = new PersonWithValidation();
        $person->setLastname('Johnson');
        $person->commit();
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionOnValidValues()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setLastname('Johnson');
        $person->setMail("address@mail.com");
        $person->setNameWithMinLength("long enough");
        $person->commit();
    }

    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenTryingToSaveTooShortValue()
    {

        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setLastname('Johnson');
        $person->setMail("address@mail.com");
        $person->setNameWithMinLength("s");
        $person->commit();

    }

    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenTryingToSaveTooLongValue()
    {

        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setLastname('Johnson');
        $person->setMail("address@mail.com");
        $person->setNameWithMaxLength("way too long");
        $person->commit();
    }

    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldBeAbleToDoRegExpValidation()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithNumericRegex("invalid");
        $person->commit();

    }
    /**
     * @test
     */
    public function shouldNotThrowExceptionOnValidRegex()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithNumericRegex("123");
        $person->commit();
    }


    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldBeAbleToSpecifyMinVal()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithMinVal(2);
        $person->commit();

    }
    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldBeAbleToSpecifyMaxVal()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithMaxVal(2000);
        $person->commit();
    }

    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenNotBetweenMinAndMaxVal()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithMinAndMaxVal(100);
        $person->commit();
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionWhenBetweenMinAndMaxVal()
    {
        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->setNameWithMinAndMaxVal(40);
        $person->commit();
    }



    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenTryingToUpdateTooLongValue()
    {

        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setLastname('Johnson');
        $person->setMail("address@mail.com");
        $person->setNameWithMaxLength("ok");
        $person->commit();

        $id = $person->getId();
        $p = new PersonWithValidation($id);
        $p->setNameWithMaxLength('Way too long');
        $p->commit();
    }
    /**
     * @test
     * @expectedException LudoDBInvalidModelDataException
     */
    public function shouldThrowExceptionWhenUpdatingRequiredValueToEmptyString()
    {

        $person = new PersonWithValidation();
        $person->setFirstName('John');
        $person->setMail("address@mail.com");
        $person->commit();

        $id = $person->getId();
        $p = new PersonWithValidation($id);
        $p->setFirstname("");
        $p->commit();
    }


}
