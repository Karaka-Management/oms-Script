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

namespace Modules\Helper\Controller;

use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Helper\Models\NullReport;
use Modules\Helper\Models\NullTemplate;
use Modules\Helper\Models\PermissionState;
use Modules\Helper\Models\Report;
use Modules\Helper\Models\ReportMapper;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateDataType;
use Modules\Helper\Models\TemplateMapper;
use Modules\Media\Models\Collection;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Tag\Models\NullTag;
use phpOMS\Account\PermissionType;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

/**
 * Helper controller class.
 *
 * @package    Modules\Helper
 * @license    OMS License 1.0
 * @link       https://orange-management.org
 * @since      1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param HttpRequest      $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiHelperExport(HttpRequest $request, ResponseAbstract $response, $data = null) : void
    {
        /** @var Template $template */
        $template  = TemplateMapper::get((int) $request->getData('id'));
        $accountId = $request->getHeader()->getAccount();

        // is allowed to read
        if (!$this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::READ, $this->app->orgId, null, self::MODULE_NAME, PermissionState::REPORT, $template->getId())
        ) {
            $response->getHeader()->setStatusCode(RequestStatusCode::R_403);

            return;
        }

        if (\in_array($request->getData('type'), ['xlsx', 'pdf', 'docx', 'pptx', 'csv'])) {
            // is allowed to export
            if (!$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::MODULE_NAME, PermissionState::EXPORT
            )) {
                $response->getHeader()->setStatusCode(RequestStatusCode::R_403);

                return;
            }

            Autoloader::addPath(__DIR__ . '/../../../Resources/');
            $response->getHeader()->setDownloadable($template->getName(), (string) $request->getData('type'));
        }

        $view = $this->createView($template, $request, $response);
        $this->setHelperResponseHeader($view, $template->getName(), $request, $response);
        $view->setData('path', __DIR__ . '/../../../');

        $response->set('export', $view);
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
        switch ($request->getData('type')) {
            case 'pdf':
                $response->getHeader()->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->getHeader()->set('Content-Type', MimeType::M_PDF, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['pdf']->getPath(), 0, -8), 'pdf.php');
                break;
            case 'csv':
                $response->getHeader()->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->getHeader()->set('Content-Type', MimeType::M_CONF, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['csv']->getPath(), 0, -8), 'csv.php');
                break;
            case 'xls':
            case 'xlsx':
                $response->getHeader()->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->getHeader()->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['excel']->getPath(), 0, -8), 'xls.php');
                break;
            case 'doc':
            case 'docx':
                $response->getHeader()->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->getHeader()->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['word']->getPath(), 0, -8), 'doc.php');
                break;
            case 'ppt':
            case 'pptx':
                $response->getHeader()->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->getHeader()->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['powerpoint']->getPath(), 0, -8), 'ppt.php');
                break;
            case 'json':
                $response->getHeader()->set('Content-Type', MimeType::M_JSON, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['json']->getPath(), 0, -9), 'json.php');
                break;
            default:
                $response->getHeader()->set('Content-Type', 'text/html; charset=utf-8');
                $view->setTemplate('/' . \substr($view->getData('tcoll')['template']->getPath(), 0, -8));
        }
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
        $tcoll = [];
        $files = $template->getSource()->getSources();

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
                    $tcoll['css'][$tMedia->getName()] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.js'):
                    $tcoll['js'][$tMedia->getName()] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.sqlite'):
                case StringUtils::endsWith($lowerPath, '.db'):
                    $tcoll['db'][$tMedia->getName()] = $tMedia;
                    break;
                default:
                    $tcoll['other'][$tMedia->getName()] = $tMedia;
            }
        }

        $view = new View($this->app->l11nManager, $request, $response);
        if (!$template->isStandalone()) {
            /** @var Report[] $report */
            $report = ReportMapper::getNewest(1,
                (new Builder($this->app->dbPool->get()))->where('helper_report.helper_report_template', '=', $template->getId())
            );

            $rcoll  = [];
            $report = \end($report);
            $report = $report === false ? new NullReport() : $report;

            if (!($report instanceof NullReport)) {
                $files = $report->getSource()->getSources();

                foreach ($files as $media) {
                    $rcoll[$media->getName() . '.' . $media->getExtension()] = $media;
                }
            }

            $view->addData('report', $report);
            $view->addData('rcoll', $rcoll);
        }

        $view->addData('tcoll', $tcoll);
        $view->addData('lang', $request->getData('lang') ?? $request->getHeader()->getL11n()->getLanguage());
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
    public function apiTemplateCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $dbFiles       = $request->getDataJson('media-list') ?? [];
        $uploadedFiles = $request->getFiles() ?? [];
        $files         = [];

        if (!empty($uploadedFiles)) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                $request->getData('name') ?? '',
                $uploadedFiles,
                $request->getHeader()->getAccount(),
                __DIR__ . '/../../../Modules/Media/Files'
            );

            foreach ($uploaded as $upload) {
                $files[] = new NullMedia($upload->getId());
            }
        }

        foreach ($dbFiles as $db) {
            $files[] = new NullMedia($db);
        }

        /** @var Collection $collection */
        $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
            (string) ($request->getData('name') ?? ''),
            (string) ($request->getData('description') ?? ''),
            $files,
            $request->getHeader()->getAccount()
        );

        $collection->setPath('/Modules/Media/Files/Modules/Helper/' . ((string) ($request->getData('name') ?? '')));
        $collection->setVirtualPath('/Modules/Helper');

        if ($collection instanceof NullCollection) {
            $response->getHeader()->setStatusCode(RequestStatusCode::R_403);
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Template', 'Couldn\'t create collection for template', null);

            return;
        }

        CollectionMapper::create($collection);

        $template = $this->createTemplateFromRequest($request, $collection->getId());

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->getHeader()->getAccount(),
                $this->app->orgId,
                $this->app->appName,
                self::MODULE_NAME,
                PermissionState::TEMPLATE,
                $template->getId(),
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->getHeader()->getAccount(),
            $request->getOrigin()
        );

        $this->createModel($request->getHeader()->getAccount(), $template, TemplateMapper::class, 'template', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Template', 'Template successfully created', $template);
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
        $expected = $request->getData('expected');

        $helperTemplate = new Template();
        $helperTemplate->setName($request->getData('name') ?? 'Empty');
        $helperTemplate->setDescription(Markdown::parse((string) ($request->getData('description') ?? '')));
        $helperTemplate->setDescriptionRaw((string) ($request->getData('description') ?? ''));

        if ($collectionId > 0) {
            $helperTemplate->setSource(new NullCollection($collectionId));
        }

        $helperTemplate->setStandalone((bool) ($request->getData('standalone') ?? false));
        $helperTemplate->setExpected(!empty($expected) ? \json_decode($expected, true) : []);
        $helperTemplate->setCreatedBy(new NullAccount($request->getHeader()->getAccount()));
        $helperTemplate->setDatatype((int) ($request->getData('datatype') ?? TemplateDataType::OTHER));
        $helperTemplate->setVirtualPath((string) ($request->getData('virtualpath') ?? '/'));

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, null);
                    $helperTemplate->addTag($internalResponse->get($request->getUri()->__toString())['response']);
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
    public function apiReportCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $files = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getData('name') ?? '',
            $request->getFiles(),
            $request->getHeader()->getAccount(),
            __DIR__ . '/../../../Modules/Media/Files'
        );

        $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
            (string) ($request->getData('name') ?? ''),
            (string) ($request->getData('description') ?? ''),
            $files,
            $request->getHeader()->getAccount()
        );

        $collection->setPath('/Modules/Media/Files/Modules/Helper/' . ((string) ($request->getData('name') ?? '')));
        $collection->setVirtualPath('/Modules/Helper');

        if ($collection instanceof NullCollection) {
            $response->getHeader()->setStatusCode(RequestStatusCode::R_403);
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Report', 'Couldn\'t create collection for report', null);

            return;
        }

        CollectionMapper::create($collection);

        $report = $this->createReportFromRequest($request, $response, $collection->getId());

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->getHeader()->getAccount(),
                $this->app->orgId,
                $this->app->appName,
                self::MODULE_NAME,
                PermissionState::REPORT,
                $report->getId(),
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->getHeader()->getAccount(),
            $request->getOrigin()
        );

        $this->createModel($request->getHeader()->getAccount(), $report, ReportMapper::class, 'report', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Report', 'Report successfully created', $report);
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
        $helperReport = new Report();
        $helperReport->setTitle((string) ($request->getData('name')));
        $helperReport->setSource(new NullCollection($collectionId));
        $helperReport->setTemplate(new NullTemplate((int) $request->getData('template')));
        $helperReport->setCreatedBy(new NullAccount($request->getHeader()->getAccount()));

        return $helperReport;
    }
}
