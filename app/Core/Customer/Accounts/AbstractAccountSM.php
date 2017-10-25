<?php
declare(strict_types=1);

namespace App\Core\Customer\Accounts;

use App\Core\SMInterface;
use App\Core\System\Machine\SMCallbackInterface;
use App\Core\System\Machine\StateEntityInterface;
use App\Core\System\Transaction\TransactionInterface;

/**
 * Class AbstractAccountSM
 * @package App\Core\Customer\Accounts
 */
abstract class AbstractAccountSM implements SMInterface
{
    /**
     * @var StateEntityInterface
     */
    private $object;

    /**
     * AbstractAccountSM constructor.
     * @param StateEntityInterface $object
     */
    public function __construct(StateEntityInterface $object)
    {
        $this->object = $object;
    }

    /**
     * @param TransactionInterface $transaction
     * @param SMCallbackInterface $callback
     * @return bool
     */
    public function apply(TransactionInterface $transaction, SMCallbackInterface $callback): bool
    {
        if ($this->can($transaction) >= 0) {
            try {
                $callback->before($transaction);
                $this->getEntity()->addBalance($transaction->getAmount());
                $this->getEntity()->getBalanceState($transaction->getType())->save();
                $callback->after($transaction);

                return true;
            } catch (\Throwable $e) {
                $callback->onError($transaction, $e);
            }
        }

        return false;
    }

    /**
     * @return StateEntityInterface
     */
    public function getEntity(): StateEntityInterface
    {
        return $this->object;
    }
}