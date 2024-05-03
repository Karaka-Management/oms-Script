<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Helper
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */
$templateList = \Modules\Script\Models\TemplateMapper::getAll()->executeGetArray();

echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <form id="helper-report-create" action="<?= UriFactory::build('{/api}helper/report/report?csrf={$CSRF}'); ?>" method="post">
                <div class="portlet-head"><?= $this->getHtml('Report'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iTitle"><?= $this->getHtml('Title'); ?></label>
                        <input id="iTitle" name="name" type="text" required>
                    </div>

                    <div class="form-group">
                        <label for="iFiles"><?= $this->getHtml('Files'); ?></label>
                        <input type="file" id="iFiles" name="files">
                    </div>

                    <div class="form-group">
                        <label for="iTemplate"><?= $this->getHtml('Template'); ?></label>
                        <select id="iTemplate" name="template">
                            <?php foreach ($templateList as $key => $value) : ?>
                            <option value="<?= (int) $key; ?>"><?= $this->printHtml($value->name); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="portlet-foot">
                    <input type="submit" id="iReportCreateButton" name="reportCreateButton" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                </div>
            </form>
        </section>
    </div>
</div>
