<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Helper
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use phpOMS\Model\Html\FormElementGenerator;
use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */

/** @var \Modules\Media\Models\Collection $tcoll */
$tcoll = $this->getData('tcoll');

/** @var \Modules\Media\Models\Collection $rcoll */
$rcoll = $this->getData('rcoll');

/** @var string $cLang */
$cLang = $this->getData('lang');

/** @var \Modules\Helper\Models\Template $template */
$template = $this->getData('template');

/** @var \Modules\Helper\Models\Report $report */
$report = $this->getData('report');

/** @noinspection PhpIncludeInspection */
/** @var array<string, array<string, string>> $reportLanguage */
$reportLanguage = isset($tcoll['lang']) ? include __DIR__ . '/../../../../' . \ltrim($tcoll['lang']->getPath(), '/') : [];

/** @var array<string, string> $lang */
$lang = $reportLanguage[$cLang] ?? [];
$settings = isset($tcoll['cfg']) ? \json_decode(\file_get_contents(__DIR__ . '/../../../../' . \ltrim($tcoll['cfg']->getPath(), '/')), true) : [];

echo $this->getData('nav')->render(); ?>
<div class="row" style="height: calc(100% - 85px);">
    <div class="col-xs-12 col-md-9">
        <div class="portlet">
            <div class="portlet-body">
                <iframe data-form="iUiSettings" data-name="iframeHelper" id="iHelperFrame" src="<?= UriFactory::build('{/api}helper/report/export/?{?}&id=' . $template->getId()); ?>&u=<?=  $this->getData('unit'); ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-3">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Reports'); ?></div>
            <div class="portlet-body">
                <form action="<?= UriFactory::build('{/api}helper/template'); ?>" method="post">
                    <table class="layout wf-100">
                        <tbody>
                        <tr>
                            <td><label for="iLang"><?= $this->getHtml('Language'); ?></label>
                        <tr>
                            <td><select id="iLang" name="lang" data-action='[{"listener": "change", "action": [{"key": 1, "type": "redirect", "uri": "{%}&lang={#iLang}", "target": "self"}]}]'>
                                    <?php foreach ($reportLanguage as $key => $language) : ?>
                                    <option value="<?= $this->printHtml($key); ?>"<?= $this->printHtml($key === $cLang ? ' selected' : ''); ?>><?= $this->printHtml($language[':language']); ?>
                                    <?php endforeach; ?>
                                </select>
                        <?php if (!$template->isStandalone()) : ?><tr>
                            <td><label for="iReport"><?= $this->getHtml('Report'); ?></label>
                        <tr>
                            <td><select id="iReport" name="report">
                                </select>
                        <?php endif; ?>
                    </table>
                </form>
            </div>
        </div>

        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Export'); ?></div>
            <div class="portlet-body">
                <form>
                    <table class="layout wf-100">
                        <tbody>
                        <tr>
                            <td><label for="iExport"><?= $this->getHtml('Export'); ?></label>
                        <tr>
                            <td><select id="iExport" name="export-type">
                                    <option value="select" disabled><?= $this->getHtml('Select'); ?>
                                    <option value="html"><?= $this->getHtml('Print'); ?>
                                    <option value="xlsx"<?= $this->printHtml((!isset($tcoll['excel'])) ? ' disabled' : ''); ?>>Excel
                                    <option value="pdf"<?= $this->printHtml((!isset($tcoll['pdf'])) ? ' disabled' : ''); ?>>Pdf
                                    <option value="docx"<?= $this->printHtml((!isset($tcoll['word'])) ? ' disabled' : ''); ?>>Word
                                    <option value="pptx"<?= $this->printHtml((!isset($tcoll['powerpoint'])) ? ' disabled' : ''); ?>>PowerPoint
                                    <option value="csv"<?= $this->printHtml((!isset($tcoll['csv'])) ? ' disabled' : ''); ?>>Csv
                                    <option value="json"<?= $this->printHtml((!isset($tcoll['json'])) ? ' disabled' : ''); ?>>Json
                                </select>
                        <tr>
                            <td><a tabindex="0" target="_blank" class="button" href="<?= UriFactory::build('{/api}helper/report/export?{?}'); ?>&type={#iExport}&lang={#iLang}{#iUiSettings}"><?= $this->getHtml('Export'); ?></a>
                    </table>
                </form>
            </div>
        </div>

        <?php if (!empty($settings)) : ?>
        <div class="portlet">
            <form id="iUiSettings">
                <div class="portlet-head"><?= $this->getHtml('Settings'); ?></div>
                <div class="portlet-body">
                    <table class="layout wf-100">
                        <tbody>
                        <?php foreach ($settings as $element) : ?>
                        <tr>
                            <td><?= FormElementGenerator::generate($element, $this->request->getData($element['attributes']['name'] ?? '')); ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="portlet-foot"><a tabindex="0" class="button" href="<?= UriFactory::build('{%}'); ?>&lang={#iLang}{#iUiSettings}"><?= $this->getHtml('Load'); ?></a></div>
            </form>
        </div>
        <?php endif; ?>

        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Info'); ?></div>
            <div class="portlet-body">
                <table class="list wf-100">
                    <tbody>
                    <?php if (!$template->isStandalone() && !($report instanceof \Modules\Helper\Models\NullReport)) : ?>
                    <tr>
                        <th colspan="2"><?= $this->getHtml('Report'); ?>
                    <tr>
                        <td><?= $this->getHtml('Name'); ?>
                        <td><?= $this->printHtml($report->title); ?>
                    <tr>
                        <td><?= $this->getHtml('Creator'); ?>
                        <td><?= $this->printHtml($report->createdBy->name1); ?>
                    <tr>
                        <td><?= $this->getHtml('Created'); ?>
                        <td><?= $report->createdAt->format('Y-m-d'); ?>
                    <?php endif; ?>
                    <tr>
                        <th colspan="2"><?= $this->getHtml('Template'); ?>
                    <tr>
                        <td><?= $this->getHtml('Name'); ?>
                        <td><?= $this->printHtml($template->name); ?>
                    <tr>
                        <td><?= $this->getHtml('Creator'); ?>
                        <td><a href="<?= UriFactory::build('{/prefix}profile/single?for=' . $template->createdBy->getId()); ?>"><?= $this->printHtml($template->createdBy->name1); ?></a>
                    <tr>
                        <td><?= $this->getHtml('Created'); ?>
                        <td><?= $template->createdAt->format('Y-m-d'); ?>
                    <tr>
                        <td><?= $this->getHtml('Tags'); ?>
                        <td>
                            <?php $tags = $template->getTags(); foreach ($tags as $tag) : ?>
                                <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= $tag->icon !== null ? '<i class="' . $this->printHtml($tag->icon ?? '') . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                            <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>