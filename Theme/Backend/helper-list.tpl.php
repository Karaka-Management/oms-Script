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

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View                $this
 * @var \Modules\Helper\Models\Template[] $templates
 */
$templates = $this->getData('reports');

/** @var \Modules\Admin\Models\Account $account */
$account = $this->getData('account');

$accountDir = $account->getId() . ' ' . $account->login;

/** @var \Modules\Media\Models\Collection[] */
$collections = $this->getData('collections');
$mediaPath   = \urldecode($this->getData('path') ?? '/');

$previous = empty($templates) ? '{/prefix}helper/list' : '{/prefix}helper/list?{?}&id=' . \reset($templates)->getId() . '&ptype=p';
$next     = empty($templates) ? '{/prefix}helper/list' : '{/prefix}helper/list?{?}&id=' . \end($templates)->getId() . '&ptype=n';

echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <ul class="crumbs-2">
                <li data-href="<?= UriFactory::build('{/prefix}helper/list?path=/Accounts/' . $accountDir); ?>"><a href="<?= UriFactory::build('{/prefix}helper/list?path=/Accounts/' . $accountDir); ?>"><i class="fa fa-home"></i></a>
                <li data-href="<?= UriFactory::build('{/prefix}helper/list?path=/'); ?>"><a href="<?= UriFactory::build('{/prefix}helper/list?path=/'); ?>">/</a></li>
                <?php
                    $subPath    = '';
                    $paths      = \explode('/', \ltrim($mediaPath, '/'));
                    $length     = \count($paths);
                    $parentPath = '';

                    for ($i = 0; $i < $length; ++$i) :
                        if ($paths[$i] === '') {
                            continue;
                        }

                        if ($i === $length - 1) {
                            $parentPath = $subPath === '' ? '/' : $subPath;
                        }

                        $subPath .= '/' . $paths[$i];

                        $url = UriFactory::build('{/prefix}helper/list?path=' . $subPath);
                ?>
                    <li data-href="<?= $url; ?>"<?= $i === $length - 1 ? 'class="active"' : ''; ?>><a href="<?= $url; ?>"><?= $this->printHtml($paths[$i]); ?></a></li>
                <?php endfor; ?>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Helpers'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table id="helperList" class="default sticky">
                <thead>
                <tr>
                    <td><label class="checkbox" for="helperList-0">
                            <input type="checkbox" id="helperList-0" name="helperselect">
                            <span class="checkmark"></span>
                        </label>
                    <td>
                    <td class="wf-100"><?= $this->getHtml('Name'); ?>
                        <label for="helperList-sort-1">
                            <input type="radio" name="helperList-sort" id="helperList-sort-1">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="helperList-sort-2">
                            <input type="radio" name="helperList-sort" id="helperList-sort-2">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Tag'); ?>
                        <label for="helperList-sort-3">
                            <input type="radio" name="helperList-sort" id="helperList-sort-3">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="helperList-sort-4">
                            <input type="radio" name="helperList-sort" id="helperList-sort-4">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Creator'); ?>
                        <label for="helperList-sort-5">
                            <input type="radio" name="helperList-sort" id="helperList-sort-5">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="helperList-sort-6">
                            <input type="radio" name="helperList-sort" id="helperList-sort-6">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Updated'); ?>
                        <label for="helperList-sort-7">
                            <input type="radio" name="helperList-sort" id="helperList-sort-7">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="helperList-sort-8">
                            <input type="radio" name="helperList-sort" id="helperList-sort-8">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                <tbody>
                <?php if (!empty($parentPath)) : $url = UriFactory::build('{/prefix}helper/list?path=' . $parentPath); ?>
                        <tr tabindex="0" data-href="<?= $url; ?>">
                            <td>
                            <td data-label="<?= $this->getHtml('Type'); ?>"><a href="<?= $url; ?>"><i class="fa fa-folder-open-o"></i></a>
                            <td data-label="<?= $this->getHtml('Name'); ?>"><a href="<?= $url; ?>">..
                            </a>
                            <td>
                            <td>
                            <td>
                            <td>
                    <?php endif; ?>
                <?php $count = 0; foreach ($collections as $key => $value) : ++$count;
                    $url     = UriFactory::build('{/prefix}helper/list?path=' . \rtrim($value->getVirtualPath(), '/') . '/' . $value->name);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><label class="checkbox" for="helperList-<?= $key; ?>">
                                    <input type="checkbox" id="helperList-<?= $key; ?>" name="helperselect">
                                    <span class="checkmark"></span>
                                </label>
                        <td><a href="<?= $url; ?>"><i class="fa fa-folder-open-o"></i></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($value->name); ?></a>
                        <td>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($value->createdBy->name1); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($value->createdAt->format('Y-m-d')); ?></a>
                <?php endforeach; ?>
                        <?php foreach ($templates as $key => $template) : ++$count;
                        $url = UriFactory::build('{/prefix}helper/report/view?{?}&id=' . $template->getId()); ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td><label class="checkbox" for="helperList-<?= $key; ?>">
                                    <input type="checkbox" id="helperList-<?= $key; ?>" name="helperselect">
                                    <span class="checkmark"></span>
                                </label>
                    <td>
                    <td data-label="<?= $this->getHtml('Name'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->name); ?></a>
                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                        <?php $tags = $template->getTags(); foreach ($tags as $tag) : ?>
                            <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= $tag->icon !== null ? '<i class="' . $this->printHtml($tag->icon ?? '') . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                        <?php endforeach; ?>
                    <td data-label="<?= $this->getHtml('Creator'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->createdBy->name1); ?></a>
                    <td data-label="<?= $this->getHtml('Updated'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->createdAt->format('Y-m-d')); ?></a>
                        <?php endforeach; ?>
                        <?php if ($count === 0) : ?>
                <tr tabindex="0" class="empty">
                    <td colspan="4"><?= $this->getHtml('Empty', '0', '0'); ?>
                        <?php endif; ?>
            </table>
            <div class="portlet-foot">
                <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
        </div>
    </div>
</div>
