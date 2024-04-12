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

namespace Modules\Helper\Controller;

use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Helper\Models\NullTemplate;
use Modules\Helper\Models\PermissionCategory;
use Modules\Helper\Models\Report;
use Modules\Helper\Models\ReportMapper;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Helper\Models\TemplateMapper;
use Modules\Media\Models\Collection;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use phpOMS\Account\PermissionType;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
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
     * Routing end-point for application behavior.
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
            $response->data['export'] = new FormValidation($val);
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
        if (!$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->unitId, null, self::NAME, PermissionCategory::REPORT, $template->id)
            || ($isExport && !$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::EXPORT))
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
        $view->data['path'] = __DIR__ . '/../../../';

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
        if (($val['id'] = !$request->hasData('id'))) {
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

        $view   = new View($this->app->l11nManager, $request, $response);
        $rcoll  = [];
        $report = null;

        if (!$template->isStandalone) {
            /** @var Report $report */
            $report = ReportMapper::get()
                ->with('template')
                ->with('source')
                ->with('source/sources')
                ->where('template', $template->id)
                ->sort('id', OrderType::DESC)
                ->limit(1)
                ->execute();

            if ($report->id > 0) {
                $files = $report->source->getSources();

                foreach ($files as $media) {
                    $rcoll[$media->name . '.' . $media->extension] = $media;
                }
            }
        }

        $view->data['report']   = $report;
        $view->data['rcoll']    = $rcoll;
        $view->data['tcoll']    = $tcoll;
        $view->data['lang']     = ISO639x1Enum::tryFromValue($request->getDataString('lang')) ?? $request->header->l11n->language;
        $view->data['template'] = $template;
        $view->data['basepath'] = __DIR__ . '/../../../';

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTemplateCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $dbFiles = $request->getDataJson('media-list');
        $files   = [];

        if (!empty($val = $this->validateTemplateCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)
                ->hasPermission(PermissionType::CREATE, $this->app->unitId, null, self::NAME, PermissionCategory::TEMPLATE)
        ) {
            $response->header->status = RequestStatusCode::R_403;
            $this->createInvalidCreateResponse($request, $response, []);

            return;
        }

        $path = $this->createHelperDir($request->getDataString('name') ?? '');

        /** @var \Modules\Media\Models\Collection $uploaded */
        $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
            names: $request->getDataList('names'),
            fileNames: $request->getDataList('filenames'),
            files: $request->files,
            account: $request->header->account,
            basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
            virtualPath: $path,
            pathSettings: PathSettings::FILE_PATH
        );

        foreach ($uploaded->sources as $upload) {
            if ($upload->id === 0) {
                continue;
            }

            $files[] = $upload;
        }

        foreach ($dbFiles as $db) {
            $files[] = new NullMedia($db);
        }

        $template = $this->createTemplateFromRequest($request, $uploaded->id);

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->header->account,
                $this->app->unitId,
                $this->app->appId,
                self::NAME,
                self::NAME,
                PermissionCategory::TEMPLATE,
                $template->id,
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->header->account,
            $request->getOrigin()
        );

        $this->createModel($request->header->account, $template, TemplateMapper::class, 'template', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $template);
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
        if (($val['name'] = !$request->hasData('name'))
            || ($val['files'] = empty($request->files))
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

        if ($request->hasData('tags')) {
            $helperTemplate->tags = $this->app->moduleManager->get('Tag', 'Api')->createTagsFromRequest($request);
        }

        return $helperTemplate;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiReportCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateReportCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(PermissionType::CREATE, $this->app->unitId, null, self::NAME, PermissionCategory::REPORT)) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $path       = '/Modules/Helper/' . ($request->getDataString('name') ?? '');
        $collection = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
            names: $request->getDataList('names'),
            fileNames: $request->getDataList('filenames'),
            files: $request->files,
            account: $request->header->account,
            basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
            virtualPath: $path
        );

        if ($collection->id < 1) {
            $response->header->status = RequestStatusCode::R_403;
            $this->createInvalidCreateResponse($request, $response, $collection);

            return;
        }

        $report = $this->createReportFromRequest($request, $response, $collection->id);

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->header->account,
                $this->app->unitId,
                $this->app->appId,
                self::NAME,
                self::NAME,
                PermissionCategory::REPORT,
                $report->id,
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->header->account,
            $request->getOrigin()
        );

        $this->createModel($request->header->account, $report, ReportMapper::class, 'report', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $report);
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
        if (($val['template'] = !$request->hasData('template'))
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
        $helperReport->title     = $request->getDataString('name') ?? '';
        $helperReport->source    = new NullCollection($collectionId);
        $helperReport->template  = new NullTemplate((int) $request->getData('template'));
        $helperReport->createdBy = new NullAccount($request->header->account);

        return $helperReport;
    }
}
