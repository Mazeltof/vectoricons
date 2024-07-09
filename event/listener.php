<?php
/**
 *
 * @package Vector Icons
 * @copyright (c) mazeltof (www.mazeland.fr)
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

namespace mazeltof\vectoricons\event;

use phpbb\extension\manager;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	protected $ext_name;
	protected $extension_manager;
	protected $phpbb_root_path;
	protected $template;
	protected $user;

	/** constructor
	 *
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user              $user
	 * @param \phpbb\extension\manager $ext_manager Extension manager
	 * @param string                   $phpbb_root_path
	 */
	public function __construct(template $template, user $user, manager $ext_manager, $phpbb_root_path)
	{
		$this->extension_manager = $ext_manager;
		$this->template = $template;
		$this->user = $user;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->ext_name = "mazeltof/vectoricons";
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after' => 'generate_paths',
		);
	}

	public function generate_paths($event)
	{
		$css_filename = 'stylesheet.css';
		$stylesheet_path = '';
		$ext_style_path = $this->extension_manager->get_extension_path($this->ext_name) . 'styles/';
		$user_css_lang_path = $ext_style_path . rawurlencode($this->user->style['style_path']) . '/theme/' . $this->user->lang_name . '/' . $css_filename;
		$user_default_css_lang_path = $ext_style_path . 'all/theme/' . $this->user->lang_name . '/' . $css_filename;

		// Get path name of all stylesheet.css present in this extension.
		$finder = $this->extension_manager->get_finder();
		$stylesheets = $finder
			->extension_suffix($css_filename)
			->extension_directory('styles')
			->find_from_extension($this->ext_name, $this->extension_manager->get_extension_path($this->ext_name, true))
		;

		foreach ($stylesheets as $stylesheet => $ext_name)
		{
			// If the file is found in the user's style we break the loop
			if ($user_css_lang_path === $stylesheet)
			{
				$stylesheet_path = $this->phpbb_root_path . $stylesheet;
				break;
			}

			// Otherwise we try to find the file in the style folder 'all' for the language of the user
			if ($user_default_css_lang_path === $stylesheet)
			{
				$stylesheet_path = $this->phpbb_root_path . $stylesheet;
			}
		}

		$this->template->assign_vars(array(
			'VECTORICONS_STYLESHEET_LANG_LINK' => $stylesheet_path,
		));
	}
}
