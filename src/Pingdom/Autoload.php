<?php

/**
 * Pingdom API PHP binding
 * (c) 2011 Dixcart Technical Solutions Limited
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Richard Benson <richard.benson@dixcart.com>
 * @link http://github.com/dixcart/php-pingdom
 * @link http://dixcart.com/it
 * @version 1
 * @license bsd
 */

/**
 * A fallback Autoload class for loading all the Pingdom API classes.
 *
 * @author Justin Rainbow <justin.rainbow@gmail.com>
 */
class Pingdom_Autoload
{
	/**
	 * Registers this instance as an autoloader.
	 *
	 * @param Boolean $prepend Whether to prepend the autoloader or not
	 */
	static public function register($prepend = false)
	{
		spl_autoload_register('Pingdom_Autoload::loadClass', true, $prepend);
	}

	/**
	 * Unregisters this instance as an autoloader.
	 */
	static public function unregister()
	{
		spl_autoload_unregister('Pingdom_Autoload::loadClass');
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $class The name of the class
	 * @return Boolean|null True, if loaded
	 */
	static public function loadClass($class)
	{
		if (0 === strpos($class, 'Pingdom_')) {
			$file = dirname(__FILE__) . '/' . str_replace('_', DIRECTORY_SEPARATOR, substr($class, 8)) . '.php';

			require $file;
			return true;
		}
	}
}