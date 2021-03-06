<?php

namespace Athens\Core\Writer;

use Athens\Core\Filter\DummyFilter;
use Athens\Core\Filter\SelectFilter;

use Twig_SimpleFilter;

use Athens\Core\WritableBearer\WritableBearerInterface;
use Athens\Core\Email\EmailInterface;
use Athens\Core\Etc\SafeString;
use Athens\Core\Field\FieldInterface;
use Athens\Core\Filter\PaginationFilter;
use Athens\Core\Filter\SortFilter;
use Athens\Core\Form\FormInterface;
use Athens\Core\Form\FormAction\FormActionInterface;
use Athens\Core\PickA\PickAFormInterface;
use Athens\Core\PickA\PickAInterface;
use Athens\Core\Section\SectionInterface;
use Athens\Core\Visitor\Visitor;
use Athens\Core\Page\PageInterface;
use Athens\Core\Row\RowInterface;
use Athens\Core\Table\TableInterface;
use Athens\Core\Settings\Settings;
use Athens\Core\Etc\StringUtils;
use Athens\Core\Link\LinkInterface;
use Athens\Core\Filter\FilterInterface;
use Athens\Core\Filter\SearchFilter;
use Athens\Core\Table\TableFormInterface;
use Athens\Core\Writable\WritableInterface;

/**
 * Class Writer is a visitor which renders Writable elements.
 *
 * @package Athens\Core\Writer
 */
class HTMLWriter extends AbstractWriter
{
    /** @var \Twig_Environment */
    protected $environment;

    /**
     * @return string[] An array containing all Framework-registered Twig template directories.
     */
    protected function getTemplatesDirectories()
    {
        return array_merge(Settings::getInstance()->getTemplateDirectories(), [dirname(__FILE__) . '/../../templates']);
    }

    /**
     * Get this Writer's Twig_Environment; create if it doesn't exist;
     *
     * @return \Twig_Environment
     */
    protected function getEnvironment()
    {
        if ($this->environment === null) {
            $loader = new \Twig_Loader_Filesystem($this->getTemplatesDirectories());
            $this->environment = new \Twig_Environment($loader);

            $filter = new Twig_SimpleFilter(
                'write',
                function (WritableInterface $host) {
                    return $host->accept($this);
                }
            );
            $this->environment->addFilter($filter);

            $filter = new Twig_SimpleFilter(
                'slugify',
                function ($string) {
                    return StringUtils::slugify($string);
                }
            );
            $this->environment->addFilter($filter);

            $filter = new Twig_SimpleFilter(
                'md5',
                function ($string) {
                    return md5($string);
                }
            );
            $this->environment->addFilter($filter);

            $filter = new Twig_SimpleFilter(
                'stripForm',
                function ($string) {
                    $string = str_replace("<form", "<div", $string);
                    $string = preg_replace('#<div class="form-actions">(.*?)</div>#', '', $string);
                    $string = str_replace("form-actions", "form-actions hidden", $string);
                    $string = str_replace(" form-errors", " form-errors hidden", $string);
                    $string = str_replace('"form-errors', '"form-errors hidden', $string);
                    $string = str_replace("</form>", "</div>", $string);
                    return $string;
                }
            );
            $this->environment->addFilter($filter);

            $filter = new Twig_SimpleFilter(
                'saferaw',
                function ($string) {
                    if ($string instanceof SafeString) {
                        $string = (string)$string;
                    } else {
                        $string = htmlentities($string);
                    }

                    return $string;
                }
            );
            $this->environment->addFilter($filter);

            $filter = new Twig_SimpleFilter(
                'writedata',
                function ($data) {
                    $string = ' ';

                    foreach ($data as $key => $value) {
                        $key = htmlentities($key);
                        $value = htmlentities($value);

                        $string .= "data-$key='$value' ";
                    }

                    return trim($string);
                }
            );
            $this->environment->addFilter($filter);

            $requestURI = array_key_exists("REQUEST_URI", $_SERVER) ? $_SERVER["REQUEST_URI"] : "";
            $this->environment->addGlobal("requestURI", $requestURI);
        }

        return $this->environment;
    }

    /**
     * Find a template by path from within the registered template directories.
     *
     * Ex: `loadTemplate("page/full_header.twig");`
     *
     * @param string $subpath
     * @return \Twig_TemplateInterface
     */
    protected function loadTemplate($subpath)
    {
        return $this->getEnvironment()->loadTemplate($subpath);
    }

    /**
     * Render $writableBearer into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param WritableBearerInterface $writableBearer
     * @return string
     */
    public function visitWritableBearer(WritableBearerInterface $writableBearer)
    {

        $template = 'writable-bearer/' . $writableBearer->getType() . '.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $writableBearer->getId(),
                    "classes" => $writableBearer->getClasses(),
                    "data" => $writableBearer->getData(),
                    "writables" => $writableBearer->getWritables(),
                ]
            );
    }

    /**
     * Render $section into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param SectionInterface $section
     * @return string
     */
    public function visitSection(SectionInterface $section)
    {

        $template = 'section/' . $section->getType() . '.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $section->getId(),
                    "classes" => $section->getClasses(),
                    "data" => $section->getData(),
                    "writables" => $section->getWritables(),
                ]
            );
    }

    /**
     * Render $link into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param LinkInterface $link
     * @return string
     */
    public function visitLink(LinkInterface $link)
    {

        $template = 'link/' . $link->getType() . '.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $link->getId(),
                    "classes" => $link->getClasses(),
                    "data" => $link->getData(),
                    "uri" => $link->getURI(),
                    "text" => $link->getText(),
                ]
            );
    }

    /**
     * Render $page into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param PageInterface $page
     * @return string
     */
    public function visitPage(PageInterface $page)
    {
        $template = 'page/' . $page->getType() . '.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $page->getId(),
                    "classes" => $page->getClasses(),
                    "data" => $page->getData(),
                    "title" => $page->getTitle(),
                    "baseHref" => $page->getBaseHref(),
                    "writables" => $page->getWritables(),
                    "projectCSS" => Settings::getInstance()->getProjectCSS(),
                    "projectJS" => Settings::getInstance()->getProjectJS(),
                ]
            );
    }

    /**
     * Render $field into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param FieldInterface $field
     * @return string
     */
    public function visitField(FieldInterface $field)
    {
        $template = 'field/' . $field->getType() . '.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "hash" => $field->getId(),
                    "classes" => $field->getClasses(),
                    "data" => $field->getData(),
                    "slug" => $field->getSlug(),
                    "initial" => $field->getInitial(),
                    "choices" => $field->getChoices(),
                    "label" => $field->getLabel(),
                    "required" => $field->isRequired(),
                    "size" => $field->getSize(),
                    "errors" => $field->getErrors(),
                    "helptext" => $field->getHelptext(),
                    "placeholder" => $field->getPlaceholder()
                ]
            );
    }

    /**
     * Render $row into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param RowInterface $row
     * @return string
     */
    public function visitRow(RowInterface $row)
    {
        $template = 'row/base.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "hash" => $row->getId(),
                    "classes" => $row->getClasses(),
                    "data" => $row->getData(),
                    "visibleFields" => $row->getFieldBearer()->getVisibleFields(),
                    "hiddenFields" => $row->getFieldBearer()->getHiddenFields(),
                    "highlightable" => $row->isHighlightable(),
                    "onClick" => $row->getOnClick(),
                ]
            );
    }

    /**
     * Render $table into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param TableInterface $table
     * @return string
     */
    public function visitTable(TableInterface $table)
    {
        $template = 'table/base.twig';

        $filters = [];
        for ($thisFilter = $table->getFilter(); $thisFilter !== null; $thisFilter = $thisFilter->getNextFilter()) {
            $filters[] = $thisFilter;
        }

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $table->getId(),
                    "classes" => $table->getClasses(),
                    "data" => $table->getData(),
                    "rows" => $table->getRows(),
                    "filters" => $filters,
                ]
            );
    }

    /**
     * Render FormInterface into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param FormInterface $form
     * @return string
     */
    public function visitForm(FormInterface $form)
    {
        $template = "form/{$form->getType()}.twig";

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $form->getId(),
                    "classes" => $form->getClasses(),
                    "data" => $form->getData(),
                    "method" => $form->getMethod(),
                    "target" => $form->getTarget(),
                    "visibleFields" => $form->getFieldBearer()->getVisibleFields(),
                    "hiddenFields" => $form->getFieldBearer()->getHiddenFields(),
                    "actions" => $form->getActions(),
                    "errors" => $form->getErrors(),
                    "subForms" => $form->getSubForms()
                ]
            );
    }

    /**
     * Render FormActionInterface into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param FormActionInterface $formAction
     * @return string
     */
    public function visitFormAction(FormActionInterface $formAction)
    {
        $template = 'form-action/base.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "label" => $formAction->getLabel(),
                    "method" => $formAction->getMethod(),
                    "target" => $formAction->getTarget(),
                ]
            );
    }

    /**
     * Render PickAInterface into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param PickAInterface $pickA
     * @return string
     */
    public function visitPickA(PickAInterface $pickA)
    {
        $template = 'pick-a/base.twig';

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $pickA->getId(),
                    "classes" => $pickA->getClasses(),
                    "data" => $pickA->getData(),
                    "manifest" => $pickA->getManifest(),
                ]
            );
    }

    /**
     * Render PickAFormInterface into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param PickAFormInterface $pickAForm
     * @return string
     */
    public function visitPickAForm(PickAFormInterface $pickAForm)
    {
        $template = "pick-a-form/{$pickAForm->getType()}.twig";

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $pickAForm->getId(),
                    "classes" => $pickAForm->getClasses(),
                    "data" => $pickAForm->getData(),
                    "method" => $pickAForm->getMethod(),
                    "target" => $pickAForm->getTarget(),
                    "manifest" => $pickAForm->getManifest(),
                    "selectedForm" => $pickAForm->getSelectedForm(),
                    "errors" => $pickAForm->getErrors()
                ]
            );
    }

    /**
     * Render TableFormInterface into html.
     *
     * This method is generally called via double-dispatch, as provided by Visitor\VisitableTrait.
     *
     * @param TableFormInterface $tableForm
     * @return string
     */
    public function visitTableForm(TableFormInterface $tableForm)
    {
        $template = "table-form/{$tableForm->getType()}.twig";

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $tableForm->getId(),
                    "classes" => $tableForm->getClasses(),
                    "data" => $tableForm->getData(),
                    "method" => $tableForm->getMethod(),
                    "target" => $tableForm->getTarget(),
                    "actions" => $tableForm->getActions(),
                    "prototypicalRow" => $tableForm->getPrototypicalRow(),
                    "canRemove" => $tableForm->getCanRemove(),
                    "rows" => $tableForm->getRows(),
                ]
            );
    }

    /**
     * Helper method to render a FilterInterface to html.
     *
     * @param FilterInterface $filter
     * @return string
     */
    public function visitFilter(FilterInterface $filter)
    {
        if ($filter instanceof DummyFilter) {
            $type = 'dummy';
        } elseif ($filter instanceof SelectFilter) {
            $type = 'select';
        } elseif ($filter instanceof SearchFilter) {
            $type = 'search';
        } elseif ($filter instanceof SortFilter) {
            $type = 'sort';
        } elseif ($filter instanceof PaginationFilter) {
            $type = 'pagination';
        } else {
            $type = 'blank';
        }
        $template = "filter/$type.twig";

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $filter->getId(),
                    "options" => $filter->getOptions(),
                ]
            );
    }

    /**
     * Render an email message into an email body, given its template.
     *
     * @param EmailInterface $email
     * @return string
     */
    public function visitEmail(EmailInterface $email)
    {
        $template = "email/{$email->getType()}.twig";

        return $this
            ->loadTemplate($template)
            ->render(
                [
                    "id" => $email->getId(),
                    "classes" => $email->getClasses(),
                    "data" => $email->getData(),
                    "message" => $email->getMessage(),
                ]
            );
    }
}
