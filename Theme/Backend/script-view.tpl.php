<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Script
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Script\Models\NullReport;
use Modules\Script\Models\NullTemplate;
use phpOMS\Model\Html\FormElementGenerator;
use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */

// @todo If no template is defined this is breaking;

/** @var \Modules\Media\Models\Collection[] $tcoll */
$tcoll = $this->data['tcoll'] ?? [];

/** @var \Modules\Media\Models\Collection[] $rcoll */
$rcoll = $this->data['rcoll'] ?? [];

/** @var string $cLang */
$cLang = $this->data['lang'] ?? 'en';

/** @var \Modules\Script\Models\Template $template */
$template = $this->data['template'] ?? new NullTemplate();

/** @var \Modules\Script\Models\Report $report */
$report = $this->data['report'] ?? new NullReport();

/** @noinspection PhpIncludeInspection */
/** @var array<string, array<string, string>> $reportLanguage */
$reportLanguage = isset($tcoll['lang']) ? include __DIR__ . '/../../../../' . \ltrim($tcoll['lang']->getPath(), '/') : [];

/** @var array<string, string> $lang */
$lang     = $reportLanguage[$cLang] ?? [];
$settings = isset($tcoll['cfg'])
    ? \json_decode(\file_get_contents(__DIR__ . '/../../../../' . \ltrim($tcoll['cfg']->getPath(), '/')), true)
    : [];

// @todo Implement direct print instead of opening a new window with
//      `document.getElementById('iHelperFrame').contentWindow.print();`
//      https://github.com/Karaka-Management/oms-Helper/issues/1

echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12 col-md-9 col-simple">
        <div class="portlet col-simple">
            <div class="portlet-body col-simple">
                <iframe class="col-simple" data-form="iUiSettings" data-name="iframeHelper" id="iHelperFrame" src="<?= UriFactory::build('{/api}script/report/export/?{?}&id=' . $template->id . '&csrf={$CSRF}'); ?>&u=<?= $this->data['unit']; ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-3">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Reports'); ?></div>
            <div class="portlet-body">
                <form action="<?= UriFactory::build('{/api}script/template?csrf={$CSRF}'); ?>" method="post">
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
                        <?php if (!$template->isStandalone) : ?><tr>
                            <td><label for="iReport"><?= $this->getHtml('Report'); ?></label>
                        <tr>
                            <td><select id="iReport" name="report">
                                </select>
                        <?php endif; ?>
                    </table>
                </form>
            </div>
        </section>

        <section class="portlet">
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
                                    <option value="xlsx"<?= $this->printHtml((isset($tcoll['excel'])) ? '' : ' disabled'); ?>>Excel
                                    <option value="pdf"<?= $this->printHtml((isset($tcoll['pdf'])) ? '' : ' disabled'); ?>>Pdf
                                    <option value="docx"<?= $this->printHtml((isset($tcoll['word'])) ? '' : ' disabled'); ?>>Word
                                    <option value="pptx"<?= $this->printHtml((isset($tcoll['powerpoint'])) ? '' : ' disabled'); ?>>PowerPoint
                                    <option value="csv"<?= $this->printHtml((isset($tcoll['csv'])) ? '' : ' disabled'); ?>>Csv
                                    <option value="json"<?= $this->printHtml((isset($tcoll['json'])) ? '' : ' disabled'); ?>>Json
                                </select>
                        <tr>
                            <td><a tabindex="0" target="_blank" class="button" href="<?= UriFactory::build('{/api}script/report/export?{?}'); ?>&type={#iExport}&lang={#iLang}{#iUiSettings}"><?= $this->getHtml('Export'); ?></a>
                    </table>
                </form>
            </div>
        </section>

        <?php if (!empty($settings)) : ?>
        <section class="portlet">
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
        </section>
        <?php endif; ?>

        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Info'); ?></div>
            <div class="portlet-body">
                <table class="list wf-100">
                    <tbody>
                    <?php if (!$template->isStandalone
                        && !($report instanceof \Modules\Script\Models\NullReport)
                    ) : ?>
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
                        <td><a href="<?= UriFactory::build('{/base}/profile/view?for=' . $template->createdBy->id); ?>"><?= $this->printHtml($template->createdBy->name1); ?></a>
                    <tr>
                        <td><?= $this->getHtml('Created'); ?>
                        <td><?= $template->createdAt->format('Y-m-d'); ?>
                    <tr>
                        <td><?= $this->getHtml('Tags'); ?>
                        <td><div class="tag-list"><?php
                            foreach ($template->tags as $tag) : ?>
                                <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>">
                                    <?= empty($tag->icon) ? '' : '<i class="g-icon">' . $this->printHtml($tag->icon) . '</i>'; ?>
                                    <?= $this->printHtml($tag->getL11n()); ?>
                                </span>
                            <?php endforeach; ?>
                            </div>
                </table>
            </div>
        </section>
    </div>
</div>
