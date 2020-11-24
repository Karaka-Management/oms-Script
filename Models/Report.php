<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Helper\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Helper\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Helper\Admin\Install\Media;
use Modules\Media\Models\Collection;
use Modules\Media\Models\NullCollection;

/**
 * Report model.
 *
 * @package Modules\Helper\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class Report implements \JsonSerializable
{
    /**
     * Report Id.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Report status.
     *
     * @var int
     * @since 1.0.0
     */
    private int $status = HelperStatus::INACTIVE;

    /**
     * Report title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Report description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $description = '';

    /**
     * Report description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $descriptionRaw = '';

    /**
     * Report created at.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Report created by.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Report template.
     *
     * @var Template
     * @since 1.0.0
     */
    private Template $template;

    /**
     * Report source.
     *
     * @var Collection
     * @since 1.0.0
     */
    private Collection $source;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->template  = new NullTemplate();
        $this->source    = new NullCollection();
    }

    /**
     * Get model id.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get the activity status
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * Set the activity status
     *
     * @param int $status Report status
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    /**
     * Get template this report belongs to
     *
     * @return Template
     *
     * @since 1.0.0
     */
    public function getTemplate() : Template
    {
        return $this->template;
    }

    /**
     * Set template this report belongs to
     *
     * @param Template $template Report template
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setTemplate(Template $template) : void
    {
        $this->template = $template;
    }

    /**
     * Set source media for the report
     *
     * @param Collection $source Report source
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setSource(Collection $source) : void
    {
        $this->source = $source;
    }

    /**
     * Get source media for the report
     *
     * @return Collection
     *
     * @since 1.0.0
     */
    public function getSource() : Collection
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'createdBy'      => $this->createdBy,
            'createdAt'      => $this->createdAt,
            'name'           => $this->title,
            'description'    => $this->description,
            'descriptionRaw' => $this->descriptionRaw,
            'status'         => $this->status,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
