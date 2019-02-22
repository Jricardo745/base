<?php

namespace FreddieGar\Base\Traits;

use FreddieGar\Base\Constants\BlameColumn;
use FreddieGar\Base\Constants\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

/**
 * Trait BlameControlTrait
 * @package FreddieGar\Base\Traits
 */
trait BlameControlTrait
{
    /**
     * The name of the "created by" column.
     * @var string
     */
    private static $CREATED_BY = BlameColumn::CREATED_BY;

    /**
     * The name of the "updated by" column.
     * @var string
     */
    private static $UPDATED_BY = BlameColumn::UPDATED_BY;

    /**
     * The name of the "deleted by" column.
     * @var string
     */
    private static $DELETED_BY = BlameColumn::DELETED_BY;

    /**
     * By default is that user guard logged
     * @var string
     */
    private static $GUARD_NAME = null;

    /**
     * By default is that user id logged
     * @var string
     */
    private static $CURRENT_USER_AUTHENTICATED = null;

    /**
     * Indicate action forever
     * @var string
     */
    private static $FOREVER = null;

    /**
     * The "booting" method of the model.
     * @return void
     */
    protected static function bootBlameControlTrait()
    {
        foreach (static::blameEvents() as $event) {
            if ($event === Event::SAVED && !self::isForever()) {
                static::{$event}(function () {
                    // When model is saving, it enable blame columns for next process
                    static::enableBlame();
                });
                continue;
            }

            if ($columns = static::blameColumnsByEvent($event)) {
                static::{$event}(function (Model $model) use ($columns, $event) {
                    foreach ($columns as $column) {
                        $model->{$column} = static::getCurrentUserAuthenticated($event, class_basename($model));
                    }
                    return true;
                });
            }
        }
    }

    /**
     *
     */
    public static function rebootBlameControlTrait()
    {
        // Un-register and reload previous events setup
        static::flushEventListeners();
        static::clearBootedModels();
    }

    /**
     * @return array
     */
    final protected static function blameEvents()
    {
        return [
            Event::CREATING,
            Event::UPDATING,
            Event::DELETING,
            Event::SAVED,
        ];
    }

    final protected static function setForever($forever)
    {
        static::$FOREVER = $forever;
    }

    final protected static function getForever()
    {
        return static::$FOREVER;
    }

    final protected static function isForever()
    {
        return self::getForever() === true;
    }

    /**
     * @param string $event
     * @return array|null
     */
    final protected static function blameColumnsByEvent($event)
    {
        $columnByEvent = [
            Event::CREATING => [
                static::$CREATED_BY,
            ],
            Event::UPDATING => [
                static::$UPDATED_BY,
            ],
            Event::DELETING => [
                static::$DELETED_BY,
            ],
        ];

        // Events without columns are eliminate
        $columnByEvent = filterArray($columnByEvent);

        return isset($columnByEvent[$event]) ? $columnByEvent[$event] : null;
    }

    /**
     * Enable blame to all columns
     * @return void
     */
    final public static function enableBlame()
    {
        static::enableCreatedBy();
        static::enableUpdatedBy();
        static::enableDeletedBy();
    }

    /**
     * Enable save created by column
     * @return void
     */
    final public static function enableCreatedBy()
    {
        static::$CREATED_BY = BlameColumn::CREATED_BY;
        static::rebootBlameControlTrait();
    }

    /**
     * Enable update updated by column
     * @return void
     */
    final public static function enableUpdatedBy()
    {
        static::$UPDATED_BY = BlameColumn::UPDATED_BY;
        static::rebootBlameControlTrait();
    }

    /**
     * Enable update deleted by column
     * @return void
     */
    final public static function enableDeletedBy()
    {
        static::$DELETED_BY = BlameColumn::DELETED_BY;
        static::rebootBlameControlTrait();
    }

    /**
     * Disable blame to all columns
     * @return void
     */
    final public static function disableBlame()
    {
        static::disableCreatedBy();
        static::disableUpdatedBy();
        static::disableDeletedBy();
    }

    /**
     * Disable save created by column
     * @param null $forever
     * @return void
     */
    final public static function disableCreatedBy($forever = null)
    {
        static::$CREATED_BY = null;
        static::rebootBlameControlTrait();
        static::setForever($forever);
    }

    /**
     * Disable update updated by column
     * @param null $forever
     * @return void
     */
    final public static function disableUpdatedBy($forever = null)
    {
        static::$UPDATED_BY = null;
        static::rebootBlameControlTrait();
        static::setForever($forever);
    }

    /**
     * Disable update deleted by column
     * @return void
     */
    final public static function disableDeletedBy()
    {
        static::$DELETED_BY = null;
        static::rebootBlameControlTrait();
    }

    /**
     * Set user to use in blame columns
     * @param int $id
     * @return void
     */
    final public static function setCurrentUserAuthenticated($id)
    {
        static::$CURRENT_USER_AUTHENTICATED = $id;
    }

    /**
     * Get guard used in blame
     * @param string $event
     * @param string $model
     * @return int
     */
    final public static function getCurrentUserAuthenticated($event, $model)
    {
        if (static::$CURRENT_USER_AUTHENTICATED) {
            return static::$CURRENT_USER_AUTHENTICATED;
        }

        if (Auth::guard(static::$GUARD_NAME)->check()) {
            static::setCurrentUserAuthenticated(Auth::guard(static::$GUARD_NAME)->id());
            return static::getCurrentUserAuthenticated($event, $model);
        }

        throw new UnauthorizedException(trans('exceptions.unauthorized', compact('event', 'model')));
    }
}
