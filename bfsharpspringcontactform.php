<?php
/**
 * @package    SharpSpring plugin for contact forms by Brainforge
 * @author    https://www.brainforge.co.uk
 * @copyright  (C) 2020 Jonathan Brain. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die('Restricted access');

class plgSystemBfsharpspringcontactform extends CMSPlugin
{
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		if ($app->isClient('administrator')) {
			return;
		}

		$user = Factory::getUser();
		if ($user->get('isRoot'))
		{
			return;
		}

		$account = trim($this->params->get('account'));
		$postback = trim($this->params->get('postback'));

		switch($app->input->get('option'))
		{
			case 'com_contact':
				if ($app->input->get('view') != 'contact')
				{
					return;
				}

				$formid = trim($this->params->get('contactformid', 'contact-form'));
				$endpoint = trim($this->params->get('contactendpoint'));
				break;
			case 'com_hikashop':
				if ($app->input->get('ctrl') != 'product' ||
					$app->input->get('task') != 'contact')
				{
					return;
				}

				$formid = trim($this->params->get('hikashopformid', 'hikashop_contact_form'));
				$endpoint = trim($this->params->get('hikashopendpoint'));
				break;
		}

		if (empty($account) || empty($postback) || empty($formid) || empty($endpoint))
		{
			return;
		}

		$formonsubmit = "__ss_noform.push(['submit', null, '" . $endpoint . "']);";

		$js = '
<script type="text/javascript">		
	var contactform = document.getElementById("' . $formid . '");
	if (contactform) {
		var contactformonsubmit = contactform.getAttribute("onsubmit");
		if (contactformonsubmit == null) {
			contactform.setAttribute("onsubmit", "' . $formonsubmit . 'return true;");
		}
		else {
			contactform.setAttribute("onsubmit", "' . $formonsubmit . '" + contactformonsubmit);
		}
	
		var __ss_noform = __ss_noform || [];
		__ss_noform.push(["baseURI", "https://app-' . $account . '.marketingautomation.services/webforms/receivePostback' . $postback . '"]);
		__ss_noform.push(["form", "' . $formid . '", "' . $endpoint . '"]);
		__ss_noform.push(["submitType", "manual"]);
	}
</script>
<script type="text/javascript" src="https://koi-' . $account . '.marketingautomation.services/client/noform.js?ver=1.24" ></script>
';

		$body = $app->getBody();
		$body = str_replace('</body>', $js . '</body>', $body);
		$app->setBody($body);
	}
}
