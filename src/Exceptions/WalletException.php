<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */
declare (strict_types = 1);
namespace Larva\Wallet\Exceptions;

use Exception;

/**
 * 交易错误
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WalletException extends Exception
{
    public function __construct($message = '', $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}