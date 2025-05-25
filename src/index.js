/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, Card, CardHeader, CardBody, CardFooter, Notice } from '@wordpress/components';
import * as Woo from '@woocommerce/components';
import { Fragment, useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import './index.scss';

// Main Armada Plugin Page Component

const MyExamplePage = () => {
	const [apiKey, setApiKey] = useState('');
	const [isSaving, setIsSaving] = useState(false);
	const [saveStatus, setSaveStatus] = useState('');
	const [errorMessage, setErrorMessage] = useState('');

	// Load the API key on component mount
	useEffect(() => {
		// First try to get the API key from the REST API settings endpoint
		apiFetch({ path: '/wp/v2/settings' }).then((settings) => {
			console.log('Settings from REST API:', settings);
			if (settings && settings.armada_plugin_api_key) {
				setApiKey(settings.armada_plugin_api_key);
			} else {
				// If not found in REST API, try to get it directly using our custom endpoint
				return apiFetch({ path: '/wp-json/armada-plugin/v1/api-key' }).catch(() => {
					// If custom endpoint fails, fallback to the option value
					return { api_key: window.armadaPluginSettings?.apiKey || '' };
				});
			}
		}).then((response) => {
			if (response && response.api_key) {
				setApiKey(response.api_key);
			}
		}).catch((error) => {
			console.error('Error loading API key:', error);
			setErrorMessage(__('Could not load API key. Please try refreshing the page.', 'armada-delivery-for-woocommerce'));
		});
	}, []);

	// Save the API key
	const saveApiKey = () => {
		setIsSaving(true);
		setSaveStatus('');
		setErrorMessage('');
		
		// Try to save using the WordPress REST API settings endpoint
		apiFetch({
			path: '/wp/v2/settings',
			method: 'POST',
			data: { armada_plugin_api_key: apiKey }
		}).then((response) => {
			console.log('Save response:', response);
			setIsSaving(false);
			setSaveStatus('success');
			
			// Verify the API key was saved
			return apiFetch({ path: '/wp/v2/settings' });
		}).then((settings) => {
			console.log('Settings after save:', settings);
			if (!settings || !settings.armada_plugin_api_key) {
				console.warn('API key not found in settings after save');
				// If not found in the response, try to save using our custom endpoint
				return apiFetch({
					path: '/wp-json/armada-plugin/v1/api-key',
					method: 'POST',
					data: { api_key: apiKey }
				}).catch(() => {
					// If custom endpoint fails, use the direct option update
					return fetch(ajaxurl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'update_armada_api_key',
							api_key: apiKey,
							nonce: window.armadaPluginSettings?.nonce || '',
						}),
					}).then(response => response.json());
				});
			}
		}).then((response) => {
			if (response && (response.success || response.api_key)) {
				setIsSaving(false);
				setSaveStatus('success');
			}
		}).catch((error) => {
			console.error('Error saving API key:', error);
			setIsSaving(false);
			setSaveStatus('error');
			setErrorMessage(__('Error saving API key. Please try again.', 'armada-delivery-for-woocommerce'));
		});
	};
	
	return (
	<Fragment>
		<Woo.Section component="article">
			<Card>
				<CardHeader>
					<h2>{__('Armada API Configuration', 'armada-delivery-for-woocommerce')}</h2>
				</CardHeader>
				<CardBody>
					<p>{__('Enter your Armada API key to connect to the Armada service.', 'armada-delivery-for-woocommerce')}</p>
					
					{errorMessage && (
						<Notice status="error" isDismissible={false}>
							{errorMessage}
						</Notice>
					)}
					
					<TextControl
						label={__('API Key', 'armada-delivery-for-woocommerce')}
						value={apiKey}
						onChange={setApiKey}
						help={__('You can find your API key in your Armada account dashboard.', 'armada-delivery-for-woocommerce')}
					/>
					
					{saveStatus === 'success' && (
						<Notice status="success" isDismissible={true} onRemove={() => setSaveStatus('')}>
							{__('API key saved successfully!', 'armada-delivery-for-woocommerce')}
						</Notice>
					)}
					
					{saveStatus === 'error' && (
						<Notice status="error" isDismissible={true} onRemove={() => setSaveStatus('')}>
							{__('Error saving API key. Please try again.', 'armada-delivery-for-woocommerce')}
						</Notice>
					)}
				</CardBody>
				<CardFooter>
					<Button 
						isPrimary 
						onClick={saveApiKey}
						isBusy={isSaving}
						disabled={isSaving}
					>
						{__('Save API Key', 'armada-delivery-for-woocommerce')}
					</Button>
				</CardFooter>
			</Card>
		</Woo.Section>
	</Fragment>
	);
};

addFilter('woocommerce_admin_pages_list', 'armada-plugin', (pages) => {
	// Add the main plugin page with API settings
	pages.push({
		container: MyExamplePage,
		path: '/armada-plugin',
		breadcrumbs: [__('Armada Plugin', 'armada-delivery-for-woocommerce')],
		navArgs: {
			id: 'armada_plugin',
			order: 70,
		},
	});

	return pages;
});
