<?php
/**
 * Karaka
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

namespace Modules\Helper\Controller;

use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Helper\Models\NullReport;
use Modules\Helper\Models\NullTemplate;
use Modules\Helper\Models\PermissionCategory;
use Modules\Helper\Models\Report;
use Modules\Helper\Models\ReportMapper;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Helper\Models\TemplateMapper;
use Modules\Media\Models\Collection;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use Modules\Tag\Models\NullTag;
use phpOMS\Account\PermissionType;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\System\MimeType;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

/**
 * Helper controller class.
 *
 * @package    Modules\Helper
 * @license    OMS License 2.0
 * @link       https://jingga.app
 * @since      1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     * @param mixed        $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiHelperExport(HttpRequest $request, HttpResponse $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateExport($request))) {
            $response->set('export', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var Template $template */
        $template = TemplateMapper::get()
            ->with('source')
            ->with('source/sources')
            ->with('reports')
            ->with('reports/source')
            ->with('reports/source/sources')
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;
        $isExport  = \in_array($request->getData('type'), ['xlsx', 'pdf', 'docx', 'pptx', 'csv', 'json']);

        // is allowed to read
        if (!$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->unitId, null, self::NAME, PermissionCategory::REPORT, $template->getId())
            || ($isExport && !$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->unitId, $this->app->appName, self::NAME, PermissionCategory::EXPORT))
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        if ($isExport) {
            Autoloader::addPath(__DIR__ . '/../../../Resources/');
            $response->header->setDownloadable($template->name, (string) $request->getData('type'));
        }

        $view = $this->createView($template, $request, $response);
        $this->setHelperResponseHeader($view, $template->name, $request, $response);
        $view->setData('path', __DIR__ . '/../../../');

        $response->set('export', $view);
    }

    /**
     * Validate export request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateExport(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = empty($request->getData('id')))) {
            return $val;
        }

        return [];
    }

    /**
     * Set header for report/template
     *
     * @param View             $view     Template view
     * @param string           $name     Template name
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    private function setHelperResponseHeader(View $view, string $name, RequestAbstract $request, ResponseAbstract $response) : void
    {
        /** @var array $tcoll */
        $tcoll = $view->getData('tcoll') ?? [];

        switch ($request->getData('type')) {
            case 'pdf':
                if (!isset($tcoll['pdf'])) {
                    break;
                }

                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_PDF, true);
                $view->setTemplate('/' . \substr($tcoll['pdf']->getPath(), 0, -8), 'pdf.php');
                break;
            case 'csv':
                if (!isset($tcoll['csv'])) {
                    break;
                }

                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_CONF, true);
                $view->setTemplate('/' . \substr($tcoll['csv']->getPath(), 0, -8), 'csv.php');
                break;
            case 'xls':
            case 'xlsx':
                if (!isset($tcoll['excel'])) {
                    break;
                }

                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($tcoll['excel']->getPath(), 0, -8), 'xls.php');
                break;
            case 'doc':
            case 'docx':
                if (!isset($tcoll['word'])) {
                    break;
                }

                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($tcoll['word']->getPath(), 0, -8), 'doc.php');
                break;
            case 'ppt':
            case 'pptx':
                if (!isset($tcoll['powerpoint'])) {
                    break;
                }

                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($tcoll['powerpoint']->getPath(), 0, -8), 'ppt.php');
                break;
            case 'json':
                if (!isset($tcoll['json'])) {
                    break;
                }

                $response->header->set('Content-Type', MimeType::M_JSON, true);
                $view->setTemplate('/' . \substr($tcoll['json']->getPath(), 0, -9), 'json.php');
                break;
            default:
                if (!isset($tcoll['template'])) {
                    break;
                }

                $response->header->set('Content-Type', 'text/html; charset=utf-8');
                $view->setTemplate('/' . \substr($tcoll['template']->getPath(), 0, -8));
        }
    }

    /**
     * Create media directory path
     *
     * @param string $name Name
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createHelperDir(string $name) : string
    {
        $dt = new \DateTime('now');

        return '/Modules/Helper/'
            . $dt->format('Y-m-d') . '_'
            . $name;
    }

    /**
     * Create view from template
     *
     * @param Template         $template Template to create view from
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return View
     *
     * @api
     *
     * @since 1.0.0
     */
    private function createView(Template $template, RequestAbstract $request, ResponseAbstract $response) : View
    {
        /** @var array{lang?:\Modules\Media\Models\Media, cfg?:\Modules\Media\Models\Media, excel?:\Modules\Media\Models\Media, word?:\Modules\Media\Models\Media, powerpoint?:\Modules\Media\Models\Media, pdf?:\Modules\Media\Models\Media, csv?:\Modules\Media\Models\Media, json?:\Modules\Media\Models\Media, template?:\Modules\Media\Models\Media, css?:array<string, \Modules\Media\Models\Media>, js?:array<string, \Modules\Media\Models\Media>, db?:array<string, \Modules\Media\Models\Media>, other?:array<string, \Modules\Media\Models\Media>} $tcoll */
        $tcoll = [];

        /** @var \Modules\Media\Models\Media[] $files */
        $files = $template->source->getSources();

        /** @var \Modules\Media\Models\Media $tMedia */
        foreach ($files as $tMedia) {
            $lowerPath = \strtolower($tMedia->getPath());

            switch (true) {
                case StringUtils::endsWith($lowerPath, '.lang.php'):
                    $tcoll['lang'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.cfg.json'):
                    $tcoll['cfg'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.xlsx.php'):
                case StringUtils::endsWith($lowerPath, '.xls.php'):
                    $tcoll['excel'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.docx.php'):
                case StringUtils::endsWith($lowerPath, '.doc.php'):
                    $tcoll['word'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.pptx.php'):
                case StringUtils::endsWith($lowerPath, '.ppt.php'):
                    $tcoll['powerpoint'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.pdf.php'):
                    $tcoll['pdf'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.csv.php'):
                    $tcoll['csv'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.json.php'):
                    $tcoll['json'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.tpl.php'):
                    $tcoll['template'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.css'):
                    if (!isset($tcoll['css'])) {
                        $tcoll['css'] = [];
                    }

                    $tcoll['css'][$tMedia->name] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.js'):
                    if (!isset($tcoll['js'])) {
                        $tcoll['js'] = [];
                    }

                    $tcoll['js'][$tMedia->name] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.sqlite'):
                case StringUtils::endsWith($lowerPath, '.db'):
                    if (!isset($tcoll['db'])) {
                        $tcoll['db'] = [];
                    }

                    $tcoll['db'][$tMedia->name] = $tMedia;
                    break;
                default:
                    if (!isset($tcoll['other'])) {
                        $tcoll['other'] = [];
                    }

                    $tcoll['other'][$tMedia->name] = $tMedia;
            }
        }

        $view = new View($this->app->l11nManager, $request, $response);
        if (!$template->isStandalone) {
            /** @var Report $report */
            $report = ReportMapper::get()
                ->with('template')
                ->with('source')
                ->with('source/sources')
                ->where('template', $template->getId())
                ->sort('id', OrderType::DESC)
                ->limit(1)
                ->execute();

            $rcoll  = [];
            $report = $report === false ? new NullReport() : $report;

            if (!($report instanceof NullReport)) {
                $files = $report->source->getSources();

                foreach ($files as $media) {
                    $rcoll[$media->name . '.' . $media->extension] = $media;
                }
            }

            $view->addData('report', $report);
            $view->addData('rcoll', $rcoll);
        }

        $view->addData('tcoll', $tcoll);
        $view->addData('lang', $request->getData('lang') ?? $request->getLanguage());
        $view->addData('template', $template);
        $view->addData('basepath', __DIR__ . '/../../../');

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTemplateCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $dbFiles       = $request->getDataJson('media-list');
        $uploadedFiles = $request->getFiles();
        $files         = [];

        if (!empty($val = $this->validateTemplateCreate($request))) {
            $response->set('template_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(PermissionType::CREATE, $this->app->unitId, null, self::NAME, PermissionCategory::TEMPLATE)) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $path = $this->createHelperDir($request->getDataString('name') ?? '');

        /** @var \Modules\Media\Models\Media[] $uploaded */
        $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getDataList('names'),
            $request->getDataList('filenames'),
            $uploadedFiles,
            $request->header->account,
            __DIR__ . '/../../../Modules/Media/Files' . $path,
            $path,
            pathSettings: PathSettings::FILE_PATH
        );

        foreach ($uploaded as $upload) {
            if ($upload instanceof NullMedia) {
                continue;
            }

            $files[] = $upload;
        }

        foreach ($dbFiles as $db) {
            $files[] = new NullMedia($db);
        }

        /** @var Collection $collection */
        $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
            $request->getDataString('name') ?? '',
            $request->getDataString('description') ?? '',
            $files,
            $request->header->account
        );

        if ($collection instanceof NullCollection) {
            $response->header->status = RequestStatusCode::R_403;
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Template', 'Couldn\'t create collection for template', null);

            return;
        }

        $collection->setPath('/Modules/Media/Files/Modules/Helper/' . ($request->getDataString('name') ?? ''));
        $collection->setVirtualPath('/Modules/Helper');

        $this->createModel($request->header->account, $collection, CollectionMapper::class, 'collection', $request->getOrigin());

        $template = $this->createTemplateFromRequest($request, $collection->getId());

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->header->account,
                $this->app->unitId,
                $this->app->appName,
                self::NAME,
                self::NAME,
                PermissionCategory::TEMPLATE,
                $template->getId(),
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->header->account,
            $request->getOrigin()
        );

        $this->createModel($request->header->account, $template, TemplateMapper::class, 'template', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Template', 'Template successfully created', $template);
    }

    /**
     * Validate template create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTemplateCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = empty($request->getData('name')))
            || ($val['files'] = empty($request->getFiles()))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create template from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return Template
     *
     * @since 1.0.0
     */
    private function createTemplateFromRequest(RequestAbstract $request, int $collectionId) : Template
    {
        $helperTemplate                 = new Template();
        $helperTemplate->name           = $request->getDataString('name') ?? '';
        $helperTemplate->description    = Markdown::parse($request->getDataString('description') ?? '');
        $helperTemplate->descriptionRaw = $request->getDataString('description') ?? '';

        if ($collectionId > 0) {
            $helperTemplate->source = new NullCollection($collectionId);
        }

        $helperTemplate->isStandalone = $request->getDataBool('standalone') ?? false;
        $helperTemplate->createdBy    = new NullAccount($request->header->account);
        $helperTemplate->virtualPath  = $request->getDataString('virtualpath') ?? '/';
        $helperTemplate->setExpected($request->getDataJson('expected'));
        $helperTemplate->setDatatype($request->getDataInt('datatype') ?? TemplateDataType::OTHER);

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, null);

                    if (!\is_array($data = $internalResponse->get($request->uri->__toString()))) {
                        continue;
                    }

                    $helperTemplate->addTag($data['response']);
                } else {
                    $helperTemplate->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        return $helperTemplate;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiReportCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateReportCreate($request))) {
            $response->set('report_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(PermissionType::CREATE, $this->app->unitId, null, self::NAME, PermissionCategory::REPORT)) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $files = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getDataList('names'),
            $request->getDataList('filenames'),
            $request->getFiles(),
            $request->header->account,
            __DIR__ . '/../../../Modules/Media/Files'
        );

        $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
            $request->getDataString('name') ?? '',
            $request->getDataString('description') ?? '',
            $files,
            $request->header->account
        );

        $collection->setPath('/Modules/Media/Files/Modules/Helper/' . ($request->getDataString('name') ?? ''));
        $collection->setVirtualPath('/Modules/Helper');

        if ($collection instanceof NullCollection) {
            $response->header->status = RequestStatusCode::R_403;
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Report', 'Couldn\'t create collection for report', null);

            return;
        }

        $this->createModel($request->header->account, $collection, CollectionMapper::class, 'collection', $request->getOrigin());

        $report = $this->createReportFromRequest($request, $response, $collection->getId());

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->header->account,
                $this->app->unitId,
                $this->app->appName,
                self::NAME,
                self::NAME,
                PermissionCategory::REPORT,
                $report->getId(),
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->header->account,
            $request->getOrigin()
        );

        $this->createModel($request->header->account, $report, ReportMapper::class, 'report', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Report', 'Report successfully created', $report);
    }

    /**
     * Validate template create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateReportCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['template'] = empty($request->getData('template')))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create report from request.
     *
     * @param RequestAbstract  $request      Request
     * @param ResponseAbstract $response     Response
     * @param int              $collectionId Id of media collection
     *
     * @return Report
     *
     * @since 1.0.0
     */
    private function createReportFromRequest(RequestAbstract $request, ResponseAbstract $response, int $collectionId) : Report
    {
        $helperReport            = new Report();
        $helperReport->title     = (string) ($request->getData('name'));
        $helperReport->source    = new NullCollection($collectionId);
        $helperReport->template  = new NullTemplate((int) $request->getData('template'));
        $helperReport->createdBy = new NullAccount($request->header->account);

        return $helperReport;
    }
}
