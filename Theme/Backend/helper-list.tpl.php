<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Helper
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View                $this
 * @var \Modules\Helper\Models\Template[] $templates
 */
$templates = $this->getData('reports');

$previous = empty($templates) ? '{/prefix}helper/list' : '{/prefix}helper/list?{?}&id=' . \reset($templates)->getId() . '&ptype=-';
$next     = empty($templates) ? '{/prefix}helper/list' : '{/prefix}helper/list?{?}&id=' . \end($templates)->getId() . '&ptype=+';

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Helpers') ?><i class="fa fa-download floatRight download btn"></i></div>
            <table class="default">
                <thead>
                <tr>
                    <td class="wf-100"><?= $this->getHtml('Name') ?>
                    <td><?= $this->getHtml('Tag'); ?>
                    <td><?= $this->getHtml('Creator') ?>
                    <td><?= $this->getHtml('Updated') ?>
                <tbody>
                <?php if (\count($templates) == 0) : ?>
                <tr class="empty">
                    <td colspan="4"><?= $this->getHtml('Empty', '0', '0'); ?>
                        <?php endif; ?>
                        <?php foreach ($templates as $key => $template) :
                        $url = UriFactory::build('{/prefix}helper/report/view?{?}&id=' . $template->getId()); ?>
                <tr data-href="<?= $url; ?>">
                    <td data-label="<?= $this->getHtml('Name') ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->getName()); ?></a>
                    <td data-label="<?= $this->getHtml('Tag') ?>">
                        <?php $tags = $template->getTags(); foreach ($tags as $tag) : ?>
                            <span class="tag" style="background: <?= $this->printHtml($tag->getColor()); ?>"><?= $this->printHtml($tag->getTitle()); ?></span>
                        <?php endforeach; ?>
                    <td data-label="<?= $this->getHtml('Creator') ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->getCreatedBy()->getName1()); ?></a>
                    <td data-label="<?= $this->getHtml('Updated') ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->getCreatedAt()->format('Y-m-d')); ?></a>
                        <?php endforeach; ?>
            </table>
            <div class="portlet-foot">
                <a class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
        </div>
    </div>
</div>
