<?php

namespace Athens\Core\Test;

use Athens\Core\Choice\ChoiceBuilder;
use PHPUnit_Framework_TestCase;

use DateTime;

use Athens\Core\Field\Field;
use Athens\Core\Field\FieldInterface;
use Athens\Core\Field\FieldBuilder;

class FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return FieldInterface[]
     */
    public function testedFields()
    {
        return [
            new Field([], [], "literal", "A Literal Field", "", ""),
        ];
    }

    public function testBuilder()
    {
        $type = "type";
        $label = "label";
        $initial = "initial";
        $required = true;
        $choices = [
            ChoiceBuilder::begin()->setValue(rand())->build(),
            ChoiceBuilder::begin()->setValue(rand())->build(),
            ChoiceBuilder::begin()->setValue(rand())->build(),
        ];
        $classes = ["class1", "class2"];
        $size = 200;
        $helptext = "h" . (string)rand();
        $placeholder = "p" . (string)rand();

        $field = FieldBuilder::begin()
            ->setType($type)
            ->setLabel($label)
            ->setInitial($initial)
            ->setRequired($required)
            ->setChoices($choices)
            ->setFieldSize($size)
            ->setHelptext($helptext)
            ->setPlaceholder($placeholder)
            ->addClass($classes[0])
            ->addClass($classes[1])
            ->build();

        $this->assertEquals($type, $field->getType());
        $this->assertEquals($label, $field->getLabel());
        $this->assertEquals($initial, $field->getInitial());
        $this->assertEquals($required, $field->isRequired());
        $this->assertEquals(array_values($choices), array_values($field->getChoices()));
        $this->assertEquals($size, $field->getSize());
        $this->assertEquals($helptext, $field->getHelptext());
        $this->assertEquals($placeholder, $field->getPlaceholder());
        $this->assertContains($classes[0], $field->getClasses());
        $this->assertContains($classes[1], $field->getClasses());
    }

    /**
     * @expectedException              \Exception
     * @expectedExceptionMessageRegExp #Must use ::setType.*#
     */
    public function testBuilderNoTypeException()
    {
        $field = FieldBuilder::begin()->build();
    }

    /**
     * @expectedException              \Exception
     * @expectedExceptionMessageRegExp #Must use ::setLabel.*#
     */
    public function testBuilderNoLabelException()
    {
        $field = FieldBuilder::begin()->setType("type")->build();
    }

    /**
     * @expectedException              \Exception
     * @expectedExceptionMessageRegExp #must include choices using ::setChoices.*#
     */
    public function testBuilderNeedsChoicesException()
    {
        $field = FieldBuilder::begin()->setType(FieldBuilder::TYPE_MULTIPLE_CHOICE)->setLabel("label")->build();
    }

    public function testGetSubmitted()
    {

        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $slug = $field->getSlug();

            $_POST[$slug] = $data;

            $this->assertEquals($data, $field->getSubmitted(), "Failure on class: " . get_class($field));
        }
    }

    public function testWasSubmitted()
    {

        foreach ($this->testedFields() as $field) {
            $slug = $field->getSlug();

            unset($_POST[$slug]);
            $this->assertEquals(false, $field->wasSubmitted(), "Failure on class: " . get_class($field));

            $_POST[$slug] = (string)rand();
            $this->assertEquals(true, $field->wasSubmitted(), "Failure on class: " . get_class($field));
        }
    }

    public function testGetSetLabel()
    {
        foreach ($this->testedFields() as $field) {
            $label = (string)rand();

            $field->setLabel($label);
            $this->assertEquals($label, $field->getLabel(), "Failure on class: " . get_class($field));
        }
    }

    public function testSetGetSize()
    {
        foreach ($this->testedFields() as $field) {
            $size = rand();

            $field->setSize($size);
            $this->assertEquals($size, $field->getSize(), "Failure on class: " . get_class($field));
        }
    }

    public function testGetSetType()
    {
        foreach ($this->testedFields() as $field) {
            $type = (string)rand();

            $field->setType($type);
            $this->assertEquals($type, $field->getType(), "Failure on class: " . get_class($field));
        }
    }

    public function testAddGetSuffixes()
    {
        foreach ($this->testedFields() as $field) {
            $suffix = (string)rand();

            $this->assertNotContains($suffix, $field->getSuffixes(), "Failure on class: " . get_class($field));
            $this->assertNotContains($suffix, $field->getSlug(), "Failure on class: " . get_class($field));

            $field->addSuffix($suffix);
            $this->assertContains($suffix, $field->getSuffixes(), "Failure on class: " . get_class($field));
            $this->assertContains($suffix, $field->getSlug(), "Failure on class: " . get_class($field));
        }

    }

    public function testAddGetPrefixes()
    {
        foreach ($this->testedFields() as $field) {
            $prefix = (string)rand();

            $this->assertNotContains($prefix, $field->getPrefixes(), "Failure on class: " . get_class($field));
            $this->assertNotContains($prefix, $field->getSlug(), "Failure on class: " . get_class($field));

            $field->addPrefix($prefix);
            $this->assertContains($prefix, $field->getPrefixes(), "Failure on class: " . get_class($field));
            $this->assertContains($prefix, $field->getSlug(), "Failure on class: " . get_class($field));
        }
    }

    public function testGetLabelSlug()
    {
        foreach ($this->testedFields() as $field) {
            $label = utf8_decode(openssl_random_pseudo_bytes(64)) . "a?b%c^d&e*f(g)h-iklmn";
            $label = str_shuffle($label);

            $field->setLabel($label);
            $slug = $field->getLabelSlug();

            // Assert that the slug is lower case
            $this->assertEquals(strtolower($slug), $slug, "Failure on class: " . get_class($field));

            // Assert that the slug has only legal characters
            $this->assertEquals(
                preg_replace(array('/[^a-zA-Z0-9 -]/','/[ -]+/','/^-|-$/'), array('','-',''), $slug),
                $slug,
                "Failure on class: " . get_class($field)
            );

            // Sanity check: assert that the slug has some length and did not pass because it's empty
            $this->assertGreaterThan(8, strlen($slug));
        }

    }

    public function testGetSlug()
    {
        foreach ($this->testedFields() as $field) {
            $label = utf8_decode(openssl_random_pseudo_bytes(64)) . "a?b%c^d&e*f(g)h-iklmn";
            $label = str_shuffle($label);

            $field->setLabel($label);
            $labelSlug = $field->getLabelSlug();

            $this->assertContains($labelSlug, $field->getSlug(), "Failure on class: " . get_class($field));
        }
    }

    public function testSetGetInitial()
    {
        foreach ($this->testedFields() as $field) {
            $initial = (string)rand();

            $this->assertNotContains($initial, $field->getInitial(), "Failure on class: " . get_class($field));

            $field->setInitial($initial);
            $this->assertContains($initial, $field->getInitial(), "Failure on class: " . get_class($field));
        }
    }

    public function testAddGetErrors()
    {
        foreach ($this->testedFields() as $field) {
            $error = (string)rand();

            $this->assertNotContains($error, $field->getErrors(), "Failure on class: " . get_class($field));

            $field->addError($error);
            $this->assertContains($error, $field->getErrors(), "Failure on class: " . get_class($field));
        }
    }

    public function testRemoveErrors()
    {
        foreach ($this->testedFields() as $field) {
            $error = (string)rand();

            $field->addError($error);
            $this->assertNotEmpty($field->getErrors(), "Failure on class: " . get_class($field));

            $field->removeErrors();
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
        }
    }

    public function testSetGetRequired()
    {
        foreach ($this->testedFields() as $field) {
            $field->setRequired(true);
            $this->assertTrue($field->isRequired(), "Failure on class: " . get_class($field));

            $field->setRequired(false);
            $this->assertFalse($field->isRequired(), "Failure on class: " . get_class($field));
        }
    }

    public function testSetGetChoices()
    {
        foreach ($this->testedFields() as $field) {
            $choices = [
                ChoiceBuilder::begin()->setValue(rand())->build(),
                ChoiceBuilder::begin()->setValue(rand())->build(),
                ChoiceBuilder::begin()->setValue(rand())->build(),
            ];
            $field->setChoices($choices);
            $this->assertEquals(
                array_values($choices),
                array_values($field->getChoices()),
                "Failure on class: " . get_class($field)
            );
        }
    }

    public function testSetGetValidatedData()
    {
        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $this->assertFalse($field->hasValidatedData());

            $field->setValidatedData($data);

            $this->assertTrue($field->hasValidatedData());
            $this->assertEquals($data, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }
    }

    public function testValidate()
    {
        $keys = ["key1", "key2"];
        $values = ["value1", "value2"];
        $choices = [
            ChoiceBuilder::begin()->setAlias($keys[0])->setValue($values[0])->build(),
            ChoiceBuilder::begin()->setAlias($keys[1])->setValue($values[1])->build(),
        ];

        // Field not required, but provided
        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $field->setRequired(false);
            $_POST[$field->getSlug()] = $data;

            $field->validate();

            $this->assertTrue($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals($data, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        // Field not required, not provided
        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $field->setRequired(false);
            unset($_POST[$field->getSlug()]);

            $field->validate();

            $this->assertTrue($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals(null, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        // Field required, and provided
        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $field->setRequired(true);
            $_POST[$field->getSlug()] = $data;

            $field->validate();

            $this->assertTrue($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals($data, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        // Field required, but not provided
        foreach ($this->testedFields() as $field) {
            $data = (string)rand();

            $field->setRequired(true);

            unset($_POST[$field->getSlug()]);

            $field->validate();

            $this->assertFalse($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertNotEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals(null, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        /* Choice Field */
        // Field has specified choices, submission does not match available choices
        foreach ($this->testedFields() as $field) {
            $field->setChoices($choices);
            $field->setType(FieldBuilder::TYPE_CHOICE);
            $data = (string)rand();
            $_POST[$field->getSlug()] = $data;

            $field->validate();

            $this->assertFalse($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertNotEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals(null, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        // Field has specified choices, submission does match available choices
        foreach ($this->testedFields() as $field) {
            $field->setChoices($choices);
            $field->setType(FieldBuilder::TYPE_CHOICE);

            $choice = array_values($field->getChoices())[0];
            $slug = array_keys($field->getChoices())[0];

            $_POST[$field->getSlug()] = $slug;

            $field->validate();

            $this->assertTrue($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals(
                $choice->getValue(),
                $field->getValidatedData(),
                "Failure on class: " . get_class($field)
            );
        }

        /* Multiple-Choice Field */
        // Field has specified choices, submission does not match available choices
        foreach ($this->testedFields() as $field) {
            $field->setChoices($choices);
            $field->setType(FieldBuilder::TYPE_MULTIPLE_CHOICE);
            $data = (string)rand();
            $_POST[$field->getSlug()] = [$data];

            $field->validate();

            $this->assertFalse($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertNotEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertEquals(null, $field->getValidatedData(), "Failure on class: " . get_class($field));
        }

        // Field has specified choices, submission does match available choices
        foreach ($this->testedFields() as $field) {
            $field->setChoices($choices);
            $field->setType(FieldBuilder::TYPE_MULTIPLE_CHOICE);

            $choice = array_values($field->getChoices())[0];
            $slug = array_keys($field->getChoices())[0];

            $_POST[$field->getSlug()] = [$slug];

            $field->validate();

            $this->assertTrue($field->isValid(), "Failure on class: " . get_class($field));
            $this->assertEmpty($field->getErrors(), "Failure on class: " . get_class($field));
            $this->assertContains(
                $choice->getValue(),
                $field->getValidatedData(),
                "Failure on class: " . get_class($field)
            );
        }
    }

    public function testFieldDatetimeWrapper()
    {
        $field = new Field([], [], "any", "label", new DateTime());

        $this->assertInstanceOf('Athens\Core\Field\DateTimeWrapper', $field->getInitial());
        $this->assertContains(date("Y") . "-", (string)$field->getInitial());
    }
}
