<?php
/*
 * Copyright (c) 2023 cclilshy
 * Contact Information:
 * Email: jingnigg@gmail.com
 * Website: https://cc.cloudtay.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * 版权所有 (c) 2023 cclilshy
 *
 * 特此免费授予任何获得本软件及相关文档文件（“软件”）副本的人，不受限制地处理
 * 本软件，包括但不限于使用、复制、修改、合并、出版、发行、再许可和/或销售
 * 软件副本的权利，并允许向其提供本软件的人做出上述行为，但须符合以下条件：
 *
 * 上述版权声明和本许可声明应包含在本软件的所有副本或主要部分中。
 *
 * 本软件按“原样”提供，不提供任何形式的保证，无论是明示或暗示的，
 * 包括但不限于适销性、特定目的的适用性和非侵权性的保证。在任何情况下，
 * 无论是合同诉讼、侵权行为还是其他方面，作者或版权持有人均不对
 * 由于软件或软件的使用或其他交易而引起的任何索赔、损害或其他责任承担责任。
 */

namespace PRipple\Framework\Facades;

use Facade\RedisWorker;
use Redis as RedisNative;


/**
 * @method static bool set(string $key, string $value, int $timeout = 0)
 * @method static string|false get(string $key)
 * @method static int strlen(string $key)
 * @method static string getRange(string $key, int $start, int $end)
 * @method static int setRange(string $key, int $offset, string $value)
 * @method static int append(string $key, string $value)
 * @method static int getBit(string $key, int $offset)
 * @method static int setBit(string $key, int $offset, bool $value)
 *
 *  List operations
 * @method static int lPush(string $key, string ...$values)
 * @method static string|array lRange(string $key, int $start, int $end)
 * @method static string lPop(string $key)
 * @method static int lLen(string $key)
 * @method static string lIndex(string $key, int $index)
 * @method static int lRem(string $key, string $value, int $count)
 * @method static bool lTrim(string $key, int $start, int $stop)
 *
 *  Set operations
 * @method static int sAdd(string $key, string ...$values)
 * @method static array sMembers(string $key)
 * @method static bool sIsMember(string $key, string $value)
 * @method static int sCard(string $key)
 * @method static array sDiff(string ...$keys)
 * @method static array sInter(string ...$keys)
 * @method static array sUnion(string ...$keys)
 *
 *  Sorted Set operations
 * @method static int zAdd(string $key, array $values)
 * @method static array zRange(string $key, int $start, int $end, bool $withscores = false)
 * @method static array zRevRange(string $key, int $start, int $end, bool $withscores = false)
 * @method static int zCard(string $key)
 * @method static int zCount(string $key, string $min, string $max)
 * @method static double zScore(string $key, string $member)
 *
 *  Hash operations
 * @method static bool hSet(string $key, string $field, string $value)
 * @method static string hGet(string $key, string $field)
 * @method static bool hDel(string $key, string ...$fields)
 * @method static int hLen(string $key)
 * @method static array hKeys(string $key)
 * @method static array hVals(string $key)
 * @method static array hMGet(string $key, array $hashKeys)
 * @method static bool hMSet(string $key, array $hashKeys)
 * @method static array hGetAll(string $key)
 *
 *  Key operations
 * @method static bool exists(string $key)
 * @method static int del(string ...$keys)
 * @method static int expire(string $key, int $seconds)
 * @method static int ttl(string $key)
 * @method static bool rename(string $oldKey, string $newKey)
 * @method static array keys(string $pattern)
 *
 *  Server operations
 * @method static string info(string $section = '')
 * @method static bool flushDB()
 * @method static bool flushAll()
 * @method static int dbSize()
 * @method static bool save()
 * @method static bool bgSave()
 *
 * @see RedisNative
 */
class Redis
{
    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return call_user_func_array([Redis::connection(), $method], $arguments);
    }

    /**
     * @param string|null $name
     * @return RedisNative|null
     */
    public static function connection(string|null $name = 'default'): RedisNative|null
    {
        return RedisWorker::getClient($name);
    }
}
