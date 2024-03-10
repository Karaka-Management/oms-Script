<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Helper
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="portlet">
            <form id="helper-template-create" action="<?= \phpOMS\Uri\UriFactory::build('{/api}helper/report/template'); ?>" method="post">
                <div class="portlet-head"><?= $this->getHtml('Template'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iTitle"><?= $this->getHtml('Title'); ?></label>
                        <input id="iTitle" name="name" type="text" placeholder="P&L Reporting" required>
                    </div>

                    <div class="form-group">
                        <label for="iDescription"><?= $this->getHtml('Description'); ?></label>
                        <?= $this->getData('editor')->render('report-editor'); ?>
                    </div>

                    <div class="form-group">
                        <?= $this->getData('editor')->getData('text')->render('report-editor', 'description', 'helper-template-create'); ?>
                    </div>

                    <div class="form-group">
                        <label class="checkbox" for="iStandalone">
                            <input type="checkbox" name="standalone" id="iStandalone" value="1" checked>
                            <span class="checkmark"></span>
                            <?= $this->getHtml('Standalone'); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="iExpected"><?= $this->getHtml('Expected'); ?></label>
                        <div class="ipt-wrap">
                            <div class="ipt-first"><input id="iExpected" type="text" placeholder="file.csv"><input name="expected" type="hidden"></div>
                            <div class="ipt-second"><button><?= $this->getHtml('Add', '0', '0'); ?></button></div>
                        </div>
                    </div>
                </div>
                <div class="portlet-foot">
                    <input type="submit" id="iReportTemplateCreateButton" name="reportTemplateCreateButton" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                </div>
            </form>
        </div>
    </div>

    <div class="col-xs-12 col-md-6">
        <?= $this->getData('media-upload')->render('helper-template-create', '/Modules/Helper'); ?>
    </div>
</div>