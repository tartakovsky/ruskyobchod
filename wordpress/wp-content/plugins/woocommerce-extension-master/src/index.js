// Import SCSS entry file so that webpack picks up changes
import './index.scss';

// Import external dependencies.
import React, { useEffect, useState } from 'react';
require( 'es6-promise' ).polyfill();

const originalFetch = require( 'isomorphic-fetch' );
const fetchRetry = require( 'fetch-retry' )( originalFetch );

import { withState } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import {
	Button,
	Card,
	CardHeader,
	CardBody,
	Heading,
	Modal,
	ToggleControl,
	Notice,
	TextControl,
	CheckboxControl,
} from '@wordpress/components';
import {
	Badge,
	Stepper,
	SectionHeader,
	Section,
	SelectControl,
	Spinner,
	Pagination,
} from '@woocommerce/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
	faExchangeAlt,
	faLink,
	faSync,
	faCheckSquare,
	faList,
	faLock,
	faCloudDownloadAlt,
	faCloudUploadAlt,
	faUnlink,
	faWindowClose,
} from '@fortawesome/free-solid-svg-icons';

import { setLocaleData, __ } from '@wordpress/i18n';

const rootUrl = dotyposGlobals.rootUrl;
const restApiUrl = dotyposGlobals.restRootUrl;

function dotyposRestHeaders( extraHeaders = {} ) {
	const headers = {
		Accept: 'application/json',
		...extraHeaders,
	};

	if (
		typeof window !== 'undefined' &&
		window.wpApiSettings &&
		window.wpApiSettings.nonce
	) {
		headers[ 'X-WP-Nonce' ] = window.wpApiSettings.nonce;
	}

	return headers;
}

function dotyposFetch( url, options = {} ) {
	return fetch( url, {
		...options,
		headers: dotyposRestHeaders( options.headers || {} ),
	} );
}

const DotyposSettings = withState( {
	actualStep: 'first',
	actualStepMovements: 'first',
	activatedInSession: false,
	connected: false,
	connectedWarehouse: false,
	licenceExpired: false,
	licenceVerified: false,
	licenceDomainInUse: false,
	licenceCannotConnect: false,
	licenceNotFound: false,
	licenceKey: '',
	componentsInactive: true,
	stepperIsPending: true,
	stepperMovementsIsPending: true,
	isConnectModalOpen: false,
	isDisconnectModalOpen: false,
	isExportCategoriesModalOpen: false,
	isImportCategoriesModalOpen: false,
	isImportProductsModalOpen: false,
	isImportProductsWizardModalOpen: false,
	isExportProductsModalOpen: false,
	isOverwriteFromDotyposModalOpen: false,
	isOverwriteFromWoocommerceModalOpen: false,
	isWarehousesModalOpen: false,
	importCategoriesClicked: false,
	exportCategoriesClicked: false,
	fetchCategoriesClicked: false,
	importProductsClicked: false,
	exportProductsClicked: false,
	pairProductsClicked: false,
	overwriteFromDotyposClicked: false,
	overwriteFromWoocommerceClicked: false,
	selectedWarehouse: null,
	newWebbhookMovementId: null,
	selectedPairWCAttribute: null,
	selectedPairDotyposAttribute: null,
	importWizardFetchedProducts: false,
	importWizardFetchingStarted: false,
	importWizardPage: 1,
	importWizardPerPage: 25,
	importWizardTotal: 0,
	warehouses: [],
	dotyposWizardProducts: [],
	pairingKeys: [],
	settings: {
		debug: false,
		licence: {
			verified: false,
			registered: false,
			expired: false,
		},
		dotypos: {
			apiKey: null,
			cloudId: null,
			warehouseId: null,
			licenceKey: null,
			webhook: {
				movement: {
					id: null,
				},
				product: {
					id: null,
					disabled: false,
				},
			},
		},
		category: {
			enabled: false,
			syncTitle: false,
		},
		product: {
			enabled: false,
			syncTitle: false,
			syncPrice: false,
			syncVat: false,
			syncCategory: false,
			syncEAN: false,
			syncNote: false,
			movement: {
				syncFromDotypos: false,
				syncToDotypos: false,
				wcPairAttribute: null,
				dotyposPairAttribute: null,
			},
		},
	},
	pairingAttributesNotSelected: false,
	warehouseNotSelected: false,
} )(
	( {
		actualStep,
		actualStepMovements,
		activatedInSession,
		componentsInactive,
		connected,
		connectedWarehouse,
		licenceExpired,
		licenceVerified,
		licenceDomainInUse,
		licenceNotFound,
		licenceCannotConnect,
		licenceKey,
		setState,
		stepperIsPending,
		stepperMovementsIsPending,
		settings,
		isConnectModalOpen,
		isDisconnectModalOpen,
		isExportCategoriesModalOpen,
		isImportCategoriesModalOpen,
		isImportProductsModalOpen,
		isImportProductsWizardModalOpen,
		isExportProductsModalOpen,
		isWarehousesModalOpen,
		isOverwriteFromDotyposModalOpen,
		isOverwriteFromWoocommerceModalOpen,
		importCategoriesClicked,
		exportCategoriesClicked,
		fetchCategoriesClicked,
		importProductsClicked,
		exportProductsClicked,
		pairProductsClicked,
		overwriteFromDotyposClicked,
		overwriteFromWoocommerceClicked,
		importWizardFetchedProducts,
		importWizardFetchingStarted,
		dotyposWizardProducts,
		importWizardPage,
		importWizardPerPage,
		importWizardTotal,
		warehouses,
		selectedWarehouse,
		warehouseNotSelected,
		newWebbhookMovementId,
		selectedPairWCAttribute,
		selectedPairDotyposAttribute,
		pairingKeys,
		pairingAttributesNotSelected,
	} ) => {
		useEffect( () => {
			loadSettings();
		}, [] );

		const settingsUrl = restApiUrl + 'dotypos/v1/settings';
		const pairingKeysUrl = restApiUrl + 'dotypos/v1/pairingKeys';
		const warehousesUrl = restApiUrl + 'dotypos/v1/dotypos/warehouses';
		const productsUrl = restApiUrl + 'dotypos/v1/dotypos/products';
		const productsImportUrl = restApiUrl + 'dotypos/v1/dotypos/products/import';
		const jobsUrl = restApiUrl + 'dotypos/v1/jobs';
		const webhookMovementUrl = restApiUrl + 'dotypos/v1/webhooks/movement';
		const webhookProductUrl = restApiUrl + 'dotypos/v1/webhooks/product';
		const registerUrl = restApiUrl + 'dotypos/v1/register';

		function wcAttributeToString(attr) {
			if(attr === '_sku') {
				return __( 'SKU', 'dotypos' );
			}
			return attr;
		}

		function setSettingsByKey( path, value ) {
			let schema = settings; // a moving reference to internal objects within obj
			const pList = path.split( '.' );
			const len = pList.length;
			for ( let i = 0; i < len - 1; i++ ) {
				const elem = pList[ i ];
				if ( ! schema[ elem ] ) schema[ elem ] = {};
				schema = schema[ elem ];
			}

			schema[ pList[ len - 1 ] ] = value;
		}

		function setConnectSettings() {
			dotyposFetch( settingsUrl )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					let connectedToWarehouse = true;
					if (
						data.product.movement.wcPairAttribute === null ||
						data.product.movement.dotyposPairAttribute === null ||
						data.dotypos.warehouseId === null
					) {
						connectedToWarehouse = false;
					}
					let connectedTo = true;
					if (
						data.dotypos.apiKey === null ||
						data.dotypos.cloudId === null
					) {
						connectedTo = false;
					}
					const actualSettings = settings;
					actualSettings.dotypos.cloudId = data.dotypos.cloudId;
					actualSettings.dotypos.apiKey = data.dotypos.apiKey;
					setState( ( state ) => ( {
						//connected: connectedTo,
						componentsInactive: ! connectedTo,
						settings: actualSettings,
						actualStep: 'third',
						activatedInSession: true,
					} ) );
				} );
		}

		function loadSettings( callback = null ) {
			dotyposFetch( settingsUrl )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					let isLicenceVerified = false;
					if ( data.licence !== null ) {
						isLicenceVerified = data.licence.registered;
					}
					let isLicenceExpired = false;
					if ( data.licence !== null ) {
						isLicenceExpired = data.licence.expired;
					}
					let connectedToWarehouse = true;
					if (
						data.product.movement.wcPairAttribute === null ||
						data.product.movement.dotyposPairAttribute === null ||
						data.dotypos.warehouseId === null
					) {
						connectedToWarehouse = false;
					}
					let connectedTo = true;
					if (
						data.dotypos.apiKey === null ||
						data.dotypos.cloudId === null ||
						isLicenceVerified === false ||
						isLicenceExpired === true
					) {
						connectedTo = false;
					}
					setState( ( state ) => ( {
						connected: connectedTo,
						componentsInactive: ! connectedTo,
						connectedWarehouse: connectedToWarehouse,
						settings: data,
						licenceVerified: isLicenceVerified,
						licenceExpired: isLicenceExpired,
					} ) );
					dotyposFetch( pairingKeysUrl )
						.then( function ( res ) {
							return res.json();
						} )
						.then( function ( keys ) {
							pairingKeys = JSON.parse( keys );
							console.log( pairingKeys );
							setState( ( state ) => ( {
								pairingKeys,
							} ) );
						} );
					console.log( settings );
					if ( callback !== null ) {
						callback();
					}
				} );
		}

		function loadWarehouses() {
			dotyposFetch( warehousesUrl )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					setState( ( state ) => ( {
						warehouses: data,
					} ) );
					console.log( warehouses );
				} );
		}

		function createJob( hook, args, callback = null ) {
			dotyposFetch( jobsUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify( {
					hook,
					args: [ args ],
					group: 'dotypos_jobs',
				} ),
			} )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					if ( callback !== null ) {
						callback();
					}
				} );
		}

		function createWebhookMovement( callback = null ) {
			dotyposFetch( webhookMovementUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
			} )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					if ( callback !== null ) {
						callback();
					}
				} );
		}

		function createWebhookProduct( callback = null ) {
			dotyposFetch( webhookProductUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
			} )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {
					if ( callback !== null ) {
						callback();
					}
				} );
		}

		async function saveSettings( callback = null ) {
			const response = await dotyposFetch( settingsUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify( settings ),
			} );
			if ( ! response.ok ) {
				// oups! something went wrong
			}
			if ( callback !== null ) {
				callback();
			}
			//const loadedSettings = response.json();
		}

		function updateAndSaveSetting( keys, values, callback = null ) {
			keys.forEach( ( key, i ) => {
				setSettingsByKey( key, values[ i ] );
			} );
			setState( ( state ) => ( {
				settings,
			} ) );
			saveSettings( callback );
		}

		const steps = [
			{
				key: 'first',
				label: __( 'Verify licence', 'dotypos' ),
				description: __(
					'First you have to enter your licence key and verify its validity.',
					'dotypos'
				),
				content: (
					<div>
						{ licenceNotFound && (
							<Notice
								status="error"
								onRemove={ () => {
									setState( ( state ) => ( {
										licenceNotFound: false,
									} ) );
								} }
							>
								{ __(
									"This key doesn't exists. Check your key.",
									'dotypos'
								) }
							</Notice>
						) }
						{ licenceDomainInUse && (
							<Notice
								status="error"
								onRemove={ () => {
									setState( ( state ) => ( {
										licenceDomainInUse: false,
									} ) );
								} }
							>
								{ __(
									'This domain is already registered.',
									'dotypos'
								) }
							</Notice>
						) }
						{ licenceCannotConnect && (
							<Notice
								status="error"
								onRemove={ () => {
									setState( ( state ) => ( {
										licenceCannotConnect: false,
									} ) );
								} }
							>
								{ __(
									'Cannot connect to licence server. Please try again later.',
									'dotypos'
								) }
							</Notice>
						) }
						<TextControl
							label="Licence key"
							value={ licenceKey }
							onChange={ ( value ) =>
								setState( ( state ) => ( {
									licenceKey: value,
								} ) )
							}
						/>
						<Button
							icon={
								<FontAwesomeIcon icon={ faLock } size="lg" />
							}
							isPrimary
							onClick={ () => {
								verifyLicence();
							} }
						>
							{ __( 'Verify', 'dotypos' ) }
						</Button>
					</div>
				),
				isComplete: licenceVerified,
			},
			{
				key: 'second',
				label: __( 'Plugin successfully installed', 'dotypos' ),
				description: __(
					'Now is right time to connect your Dotypos with Woocommerce',
					'dotypos'
				),
				content: (
					<div>
						<Notice status="success" onRemove={ () => {} }>
							{ __(
								' Your licence key were successfully verified. Thank you.',
								'dotypos'
							) }
						</Notice>
						<p>
							{ __(
								'Now is right time to connect your Dotypos with Woocommerce',
								'dotypos'
							) }
						</p>
						<Button
							icon={
								<FontAwesomeIcon icon={ faLink } size="lg" />
							}
							isPrimary
							onClick={ () => {
								const win = window;
								const h = 600;
								const w = 700;
								let windowObjectReference;
								const y =
									win.top.outerHeight / 2 +
									win.top.screenY -
									h / 2;
								const x =
									win.top.outerWidth / 2 +
									win.top.screenX -
									w / 2;
								const windowFeatures = `menubar=yes,location=0,resizable=yes,scrollbars=yes,status=yes,height=${ h },width=${ w }, top=${ y }, left=${ x }`;
								windowObjectReference = window.open(
									connectUrl,
									'_blank',
									windowFeatures
								);
								var timer = setInterval( function () {
									if ( windowObjectReference.closed ) {
										clearInterval( timer );
										setConnectSettings();
										//window.location.reload();
									}
								}, 500 );
							} }
						>
							{ __( 'Connect with DTK', 'dotypos' ) }
						</Button>
					</div>
				),
			},
			{
				key: 'third',
				label: __( 'Connect with Dotypos', 'dotypos' ),
				description: '',
				content: (
					<div>
						<Notice
							status="success"
							onRemove={ () => {
								setState( ( state ) => ( {
									connected: true,
								} ) );
							} }
						>
							{ __(
								' Successfully connected to Dotypos. Now you can configure your integration.',
								'dotypos'
							) }
						</Notice>
					</div>
				),
				isComplete: licenceVerified && activatedInSession,
			},
		];

		const stepsMovements = [
			{
				key: 'first',
				label: __( 'Select warehouse to pair', 'dotypos' ),
				description: __(
					'Now is right time to choose which Dotypos warehouse will be synced with Woocommerce',
					'dotypos'
				),
				content: (
					<div>
						<p>
							{ __(
								'Now is right time to choose which Dotypos warehouse will be synced with Woocommerce',
								'dotypos'
							) }
						</p>
						<Button
							isPrimary
							onClick={ () => {
								openWarehousesModal();
							} }
						>
							{ __( 'Select warehouse', 'dotypos' ) }
						</Button>
					</div>
				),
			},
			{
				key: 'second',
				label: __( 'Choose pairing attribute', 'dotypos' ),
				description: '',
				content: (
					<div className={ 'pair-attribute' }>
						{ pairingAttributesNotSelected && (
							<Notice status="error">
								{ __(
									'You have to select both pairing attributes',
									'dotypos'
								) }
							</Notice>
						) }
						<SelectControl
							className={ 'pair-attribute-select' }
							label={ __( 'Woocommerce', 'dotypos' ) }
							onChange={ ( selected ) => {
								if ( selected === null || selected === '' ) {
									setState( {
										pairingAttributesNotSelected: true,
									} );
								}
								setState( {
									selectedPairWCAttribute: selected,
								} );
							} }
							options={ pairingKeys.map( ( item ) => {
								return {
									key: item,
									label: wcAttributeToString(item),
									value: item,
								};
							} ) }
							placeholder={ __(
								'Start typing to filter options…',
								'dotypos'
							) }
							selected={ selectedPairWCAttribute }
						/>
						&nbsp;
						<FontAwesomeIcon icon={ faExchangeAlt } size="lg" />
						&nbsp;
						<SelectControl
							className={ 'pair-attribute-select' }
							label={ __( 'Dotypos', 'dotypos' ) }
							onChange={ ( selected ) => {
								if ( selected === null || selected === '' ) {
									setState( {
										pairingAttributesNotSelected: true,
									} );
								}
								setState( {
									selectedPairDotyposAttribute: selected,
								} );
							} }
							options={ [
								{
									key: 'ean',
									label: __( 'EAN', 'dotypos' ),
									value: 'ean',
								},
							] }
							placeholder={ __(
								'Start typing to filter options…',
								'dotypos'
							) }
							selected={ selectedPairDotyposAttribute }
						/>
						<br />
						<Button
							isPrimary
							onClick={ () => {
								choosePairingAttributes();
							} }
						>
							{ __( 'Set up pairing', 'dotypos' ) }
						</Button>
					</div>
				),
			},
			{
				key: 'third',
				label: __( 'Completed', 'dotypos' ),
				description: __(
					'Warehouse synchronization successfully set up.',
					'dotypos'
				),
				content: (
					<div>
						<p>
							{ __(
								'Warehouse synchronization successfully set up.',
								'dotypos'
							) }
						</p>
					</div>
				),
				isCompleted: true,
			},
		];

		const connectUrl =
			'http://admin.dotykacka.cz/client/connect?client_id=dotypos_woocommerce&client_secret=st2go3Yk0UeWkVfNXpUV&scope=*&redirect_uri=' +
			encodeURIComponent(
				rootUrl + 'admin.php?page=wc-admin&path=%2Fdotypos-settings-connect'
			);

		const openConnectModal = () => {
			setState( ( state ) => ( {
				isConnectModalOpen: true,
			} ) );
		};

		const closeConnectModal = () => {
			setState( ( state ) => ( {
				actualStep: 'second',
				componentsInactive: false,
				stepperIsPending: false,
				isConnectModalOpen: false,
			} ) );
		};

		const openWarehousesModal = () => {
			//Get warehouses from REST API
			loadWarehouses();
			setState( ( state ) => ( {
				isWarehousesModalOpen: true,
			} ) );
		};

		const closeWarehousesModal = () => {
			setState( ( state ) => ( {
				isWarehousesModalOpen: false,
			} ) );
		};

		const verifyLicence = () => {
			const actualSettings = settings;
			actualSettings.dotypos.licenceKey = licenceKey;
			updateAndSaveSetting(
				[ 'dotypos.licenceKey' ],
				[ licenceKey ],
				() => {
					registerLicence();
				}
			);
		};

		function registerLicence( callback = null ) {
			dotyposFetch( registerUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
			} )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( status ) {
					console.log( status );
					if ( status == 'OK' ) {
						setState( ( state ) => ( {
							actualStep: 'second',
							licenceVerified: true,
						} ) );
					} else if ( status == 'ALREADY_IN_USE' ) {
						setState( ( state ) => ( {
							licenceDomainInUse: true,
						} ) );
					} else if ( status == 'KEY_NOT_FOUND' ) {
						setState( ( state ) => ( {
							licenceNotFound: true,
						} ) );
					} else if ( status == 'CANNOT_CONNECT' ) {
						setState( ( state ) => ( {
							licenceCannotConnect: true,
						} ) );
					}
					if ( callback !== null ) {
						callback();
					}
				} );
		}

		const chooseWarehouse = () => {

			const warehouseId = selectedWarehouse;
			const actualSettings = settings;
			actualSettings.dotypos.warehouseId = warehouseId;
			if (
				( settings.dotypos.warehouseId !== null && selectedWarehouse !== null )
			) {
				updateAndSaveSetting(
					['dotypos.warehouseId'],
					[warehouseId],
					() => {
						createWebhookProduct(() => {
							createWebhookMovement();
						});
					}
				);
				setState((state) => ({
					actualStepMovements: 'second',
					warehouseNotSelected: false
				}));
				closeWarehousesModal();
			}
			else {
				setState((state) => ({
					warehouseNotSelected: true
				}));
			}
		};

		const choosePairingAttributes = () => {
			if (
				( ( settings.product.movement.dotyposPairAttribute !== null &&
					settings.product.movement.dotyposPairAttribute !== '' ) ||
					( selectedPairDotyposAttribute !== null &&
						selectedPairDotyposAttribute !== '' ) ) &&
				( ( settings.product.movement.wcPairAttribute !== null &&
					settings.product.movement.wcPairAttribute !== '' ) ||
					( selectedPairWCAttribute !== null &&
						selectedPairWCAttribute !== '' ) )
			) {
				updateAndSaveSetting(
					[
						'product.movement.dotyposPairAttribute',
						'product.movement.wcPairAttribute',
					],
					[ selectedPairDotyposAttribute, selectedPairWCAttribute ]
				);
				setState( ( state ) => ( {
					actualStepMovements: 'third',
					connectedWarehouse: true,
					pairingAttributesNotSelected: false,
				} ) );
			} else {
				setState( ( state ) => ( {
					pairingAttributesNotSelected: true,
				} ) );
			}
		};

		const openDisconnectModal = () => {
			setState( ( state ) => ( {
				isDisconnectModalOpen: true,
			} ) );
		};

		const closeDisconnectModal = () => {
			setState( ( state ) => ( {
				isDisconnectModalOpen: false,
			} ) );
		};

		const DisconnectModal = () => {
			return (
				<>
					{ isDisconnectModalOpen && (
						<Modal
							title={ __(
								'Disconnect from Dotypos?',
								'dotypos'
							) }
							onRequestClose={ () => closeDisconnectModal() }
						>
							<p>
								{ __(
									'This will cancel your Dotypos connection. You will have to reconnect for correct extension functionality.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () => closeDisconnectModal() }
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => disconnectDotypos() }
							>
								{ __( 'Disconnect', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const WarehousesModal = () => {
			return (
				<>
					{ isWarehousesModalOpen && (
						<Modal
							title={ __( 'Choose warehouse', 'dotypos' ) }
							onRequestClose={ () => closeWarehousesModal() }
							className={ 'warehouses-modal' }
						>
							{ warehouseNotSelected && (
								<Notice status="error">
									{ __(
										'You have to select any warehouse.',
										'dotypos'
									) }
								</Notice>
							) }
							<SelectControl
								label={ __( 'Pair with warehouse', 'dotypos' ) }
								onChange={ ( selected ) => {
									if (
										selected === null ||
										selected === ''
									) {
										setState( {
											warehouseNotSelected: true,
										} );
									}
									setState( { selectedWarehouse: selected } );
								} }
								options={ warehouses.map( ( warehouse ) => {
									return {
										key: warehouse.id,
										label: warehouse.name,
										value: warehouse.id,
									};
								} ) }
								placeholder={ __(
									'Start typing to filter options…',
									'dotypos'
								) }
								selected={ selectedWarehouse }
							/>
							<br />
							<Button
								isPrimary
								onClick={ () => chooseWarehouse() }
							>
								{ __( 'Choose', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const openExportCategoriesModal = () => {
			setState( ( state ) => ( {
				isExportCategoriesModalOpen: true,
			} ) );
		};

		const closeExportCategoriesModal = () => {
			setState( ( state ) => ( {
				isExportCategoriesModalOpen: false,
			} ) );
		};

		const exportCategories = () => {
			createJob( 'dotypos_job_export_categories', [] );
			closeExportCategoriesModal();
			setState( ( state ) => ( {
				exportCategoriesClicked: true,
			} ) );
		};

		const ExportCategoriesModal = () => {
			return (
				<>
					{ isExportCategoriesModalOpen && (
						<Modal
							title={ __(
								'Export categories to Dotypos?',
								'dotypos'
							) }
							onRequestClose={ () =>
								closeExportCategoriesModal()
							}
						>
							<p>
								{ __(
									'This will create Woocommerce categories in your Dotypos.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () => closeExportCategoriesModal() }
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => exportCategories() }
							>
								{ __( 'Export', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const openImportCategoriesModal = () => {
			setState( ( state ) => ( {
				isImportCategoriesModalOpen: true,
			} ) );
		};

		const closeImportCategoriesModal = () => {
			setState( ( state ) => ( {
				isImportCategoriesModalOpen: false,
			} ) );
		};

		const importCategories = () => {
			createJob( 'dotypos_job_import_categories', [] );
			closeImportCategoriesModal();
			setState( ( state ) => ( {
				importCategoriesClicked: true,
			} ) );
		};

		const fetchCategories = () => {
			createJob( 'dotypos_job_fetch_categories', [] );
			setState( ( state ) => ( {
				fetchCategoriesClicked: true,
			} ) );
		};

		const resetPairing = () => {
			updateAndSaveSetting(
				[
					'product.movement.dotyposPairAttribute',
					'product.movement.wcPairAttribute',
					'dotypos.warehouseId',
				],
				[ null, null, null ]
			);
			setState( ( state ) => ( {
				actualStepMovements: 'first',
				connectedWarehouse: false,
			} ) );
		};

		const disconnectDotypos = () => {
			updateAndSaveSetting(
				[
					'dotypos.cloudId',
					'dotypos.apiKey',
					'category.enabled',
					'product.enabled',
				],
				[ null, null, false, false ]
			);
			setState( ( state ) => ( {
				actualStep: 'first',
				connected: false,
				componentsInactive: true,
			} ) );
		};

		const ImportCategoriesModal = () => {
			return (
				<>
					{ isImportCategoriesModalOpen && (
						<Modal
							title={ __(
								'Import categories to Woocommerce?',
								'dotypos'
							) }
							onRequestClose={ () =>
								closeImportCategoriesModal()
							}
						>
							<p>
								{ __(
									'This will create Dotypos categories in your Woocommerce.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () => closeImportCategoriesModal() }
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => importCategories() }
							>
								{ __( 'Import', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const EnableCategoriesToggle = () => (
			<div>
				<ToggleControl
					label={ __( 'Enable synchronization', 'dotypos' ) }
					help={ __(
						'Your categories are synced between Woocommerce and Dotypos',
						'dotypos'
					) }
					checked={ settings.category.enabled }
					disabled={ componentsInactive }
					onChange={ () => {
						updateAndSaveSetting(
							[ 'category.enabled' ],
							[ ! settings.category.enabled ]
						);
					} }
				/>
				{ settings.category.enabled && (
					<div>
						<SectionHeader title={ __( 'Settings', 'dotypos' ) } />
						<Section component={ false }>
							<ToggleControl
								label={ __( 'Synchronize title', 'dotypos' ) }
								help={ __(
									'Automatically synchronize title of category when changed in WooCommerce.',
									'dotypos'
								) }
								checked={ settings.category.syncTitle }
								onChange={ () => {
									updateAndSaveSetting(
										[ 'category.syncTitle' ],
										[ ! settings.category.syncTitle ]
									);
								} }
							/>
						</Section>
						<SectionHeader title={ __( 'Actions', 'dotypos' ) } />
						<Section component={ false }>
							<Button
								icon={
									importCategoriesClicked ? (
										<FontAwesomeIcon
											icon={ faCheckSquare }
											size="sm"
										/>
									) : (
										<FontAwesomeIcon
											icon={ faCloudDownloadAlt }
											size="sm"
										/>
									)
								}
								isPrimary
								disabled={ importCategoriesClicked }
								onClick={ () => {
									openImportCategoriesModal();
								} }
								isSmall={ true }
							>
								{ importCategoriesClicked
									? __( 'Import requested', 'dotypos' )
									: __( 'Import from Dotypos', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								icon={
									exportCategoriesClicked ? (
										<FontAwesomeIcon
											icon={ faCheckSquare }
											size="sm"
										/>
									) : (
										<FontAwesomeIcon
											icon={ faCloudUploadAlt }
											size="sm"
										/>
									)
								}
								isPrimary
								disabled={ exportCategoriesClicked }
								onClick={ () => {
									openExportCategoriesModal();
								} }
								isSmall={ true }
							>
								{ exportCategoriesClicked
									? __( 'Export requested', 'dotypos' )
									: __( 'Export to Dotypos', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								icon={
									fetchCategoriesClicked ? (
										<FontAwesomeIcon
											icon={ faCheckSquare }
											size="sm"
										/>
									) : (
										<FontAwesomeIcon
											icon={ faSync }
											size="sm"
										/>
									)
								}
								isSecondary
								disabled={ fetchCategoriesClicked }
								onClick={ () => {
									fetchCategories();
								} }
								isSmall={ true }
							>
								{ fetchCategoriesClicked
									? __( 'Fetch requested', 'dotypos' )
									: __( 'Fetch from Dotypos', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								icon={
									<FontAwesomeIcon
										icon={ faList }
										size="sm"
									/>
								}
								isSecondary
								href={
									'/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product'
								}
								isSmall={ true }
							>
								{ __( 'List categories', 'dotypos' ) }
							</Button>
							<ExportCategoriesModal />
							<ImportCategoriesModal />
						</Section>
					</div>
				) }
			</div>
		);

		const openImportProductsModal = () => {
			setState( ( state ) => ( {
				isImportProductsModalOpen: true,
			} ) );
		};

		const closeImportProductsModal = () => {
			setState( ( state ) => ( {
				isImportProductsModalOpen: false,
			} ) );
		};

		const openImportProductsWizardModal = () => {
			setState( ( state ) => ( {
				isImportProductsWizardModalOpen: true,
			} ) );
		};

		const fetchProductsForImportWizard = () => {
			setState( ( state ) => ( {
				importWizardFetchingStarted: true,
			} ) );
			importProductsWizardFetchProducts();
		};

		const closeImportProductsWizardModal = () => {
			setState( ( state ) => ( {
				isImportProductsWizardModalOpen: false,
			} ) );
		};

		const importProducts = () => {
			createJob( 'dotypos_job_import_products', [] );
			closeImportProductsModal();
			closeImportProductsWizardModal();
			setState( ( state ) => ( {
				importProductsClicked: true,
			} ) );
		};

		const importProductsFromWizard = () => {
			dotyposFetch( productsImportUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify( dotyposWizardProducts ),
			} )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( data ) {} );
			closeImportProductsWizardModal();
			setState( ( state ) => ( {
				importProductsClicked: true,
			} ) );
		};

		const importProductsWizardFetchProducts = () => {
			createJob( 'dotypos_job_import_products_from_wizard', [] );
			fetchRetry( productsUrl, {
				//retryDelay: function(attempt, error, response) {
				//	return Math.pow(2, attempt) * 1000; // 1000, 2000, 4000
				//},
				retries: 5000,
				retryDelay: 5000,
				retryOn: [ 423 ],
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					console.log( json );
					const products = JSON.parse( json );
					console.log( products );
					setState( ( state ) => ( {
						dotyposWizardProducts: products,
						importWizardFetchedProducts: true,
					} ) );
				} );
		};

		const openExportProductsModal = () => {
			setState( ( state ) => ( {
				isExportProductsModalOpen: true,
			} ) );
		};

		const closeExportProductsModal = () => {
			setState( ( state ) => ( {
				isExportProductsModalOpen: false,
			} ) );
		};

		const exportProducts = () => {
			createJob( 'dotypos_job_export_products', [] );
			closeExportProductsModal();
			setState( ( state ) => ( {
				exportProductsClicked: true,
			} ) );
		};

		const openOverwriteFromDotyposModal = () => {
			setState( ( state ) => ( {
				isOverwriteFromDotyposModalOpen: true,
			} ) );
		};

		const closeOverwriteFromDotyposModal = () => {
			setState( ( state ) => ( {
				isOverwriteFromDotyposModalOpen: false,
			} ) );
		};

		const overwriteFromDotypos = () => {
			createJob( 'dotypos_job_overwrite_from_dotypos', [] );
			closeOverwriteFromDotyposModal();
			setState( ( state ) => ( {
				overwriteFromDotyposClicked: true,
			} ) );
		};

		const openOverwriteFromWoocommerceModal = () => {
			setState( ( state ) => ( {
				isOverwriteFromWoocommerceModalOpen: true,
			} ) );
		};

		const closeOverwriteFromWoocommerceModal = () => {
			setState( ( state ) => ( {
				isOverwriteFromWoocommerceModalOpen: false,
			} ) );
		};

		const overwriteFromWoocommerce = () => {
			createJob( 'dotypos_job_overwrite_from_woocommerce', [] );
			closeOverwriteFromWoocommerceModal();
			setState( ( state ) => ( {
				overwriteFromWoocommerceClicked: true,
			} ) );
		};

		const pairProducts = () => {
			createJob( 'dotypos_job_pair_products', [] );
			//closeExportProductsModal();
			setState( ( state ) => ( {
				pairProductsClicked: true,
			} ) );
		};

		const ImportProductsModal = () => {
			return (
				<>
					{ isImportProductsModalOpen && (
						<Modal
							title={ __(
								'Import products to Woocommerce?',
								'dotypos'
							) }
							onRequestClose={ () => closeImportProductsModal() }
						>
							<p>
								{ __(
									'This will create Dotypos products in your Woocommerce.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () => closeImportProductsModal() }
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => importProducts() }
							>
								{ __( 'Import', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const ImportProductsWizardModal = () => {
			return (
				<>
					{ isImportProductsWizardModalOpen && (
						<Modal
							title={ __( 'Import products wizard', 'dotypos' ) }
							onRequestClose={ () =>
								closeImportProductsWizardModal()
							}
							className={ 'import-wizard-modal' }
						>
							<div className={ 'import-wizard-wrapper' }>
								{ ! importWizardFetchingStarted && (
									<p>
										<Button
											className={
												'import-wizard-fetch-btn'
											}
											isSecondary
											onClick={ () =>
												fetchProductsForImportWizard()
											}
										>
											<FontAwesomeIcon
												icon={ faSync }
												size={ 'lg' }
											/>
											&nbsp;
											{ __(
												'Fetch products',
												'dotypos'
											) }
										</Button>
									</p>
								) }
								{ importWizardFetchingStarted &&
									! importWizardFetchedProducts && (
										<p>
											<Spinner />
										</p>
									) }
								{ importWizardFetchingStarted &&
									importWizardFetchedProducts &&
									dotyposWizardProducts.length === 0 && (
										<h3>
											{ __(
												'No products to import.',
												'dotypos'
											) }
										</h3>
									) }
								{ importWizardFetchedProducts &&
									dotyposWizardProducts.length > 0 && (
										<div>
											<h3>
												{ __( 'Products', 'dotypos' ) }
												&nbsp;
												<Badge
													count={
														dotyposWizardProducts.length
													}
												/>
											</h3>
											<table
												className={
													'import-wizard-table'
												}
											>
												<tr>
													<th>
														{ __(
															'Dotypos ID',
															'dotypos'
														) }
													</th>
													<th>
														{ __(
															'Name',
															'dotypos'
														) }
													</th>
													<th>
														{ __(
															'Pairing attribute',
															'dotypos'
														) }
													</th>
													<th>
														{ __(
															'Import',
															'dotypos'
														) }
													</th>
													<th>
														{ __(
															'Pair',
															'dotypos'
														) }
													</th>
												</tr>
												{ dotyposWizardProducts
													.slice(
														( importWizardPage -
															1 ) *
															importWizardPerPage,
														importWizardPage *
															importWizardPerPage
													)
													.map( ( product, i ) => (
														<tr>
															<td>
																{ product.id }
															</td>
															<td>
																{ product.name }
															</td>
															<td>
																{
																	product.pairing_attribute
																}
															</td>
															<td>
																<CheckboxControl
																	label=""
																	checked={
																		product.import
																	}
																	onChange={ (
																		value
																	) => {
																		dotyposWizardProducts[
																			( importWizardPage -
																				1 ) *
																				importWizardPerPage +
																				i
																		].import = value;
																		setState(
																			(
																				state
																			) => ( {
																				dotyposWizardProducts,
																			} )
																		);
																	} }
																/>
															</td>
															<td>
																<CheckboxControl
																	label=""
																	checked={
																		product.pair
																	}
																	onChange={ (
																		value
																	) => {
																		dotyposWizardProducts[
																			( importWizardPage -
																				1 ) *
																				importWizardPerPage +
																				i
																		].pair = value;
																		setState(
																			(
																				state
																			) => ( {
																				dotyposWizardProducts,
																			} )
																		);
																	} }
																/>
															</td>
														</tr>
													) ) }
											</table>
										</div>
									) }
								{ importWizardFetchedProducts &&
									dotyposWizardProducts.length > 0 && (
										<Pagination
											onPageChange={ (
												page,
												direction
											) => {
												setState( ( state ) => ( {
													importWizardPage: page,
												} ) );
											} }
											page={ importWizardPage }
											perPage={ importWizardPerPage }
											total={
												dotyposWizardProducts.length
											}
											showPerPagePicker={ false }
										/>
									) }
							</div>
							<Button
								isSecondary
								onClick={ () =>
									closeImportProductsWizardModal()
								}
							>
								<FontAwesomeIcon icon={ faWindowClose } />
								&nbsp;
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							{ importWizardFetchedProducts &&
								dotyposWizardProducts.length > 0 && (
									<span>
										&nbsp;
										<Button
											isPrimary
											onClick={ () =>
												importProductsFromWizard()
											}
										>
											<FontAwesomeIcon
												icon={ faCheckSquare }
											/>
											&nbsp;
											{ __(
												'Import selected',
												'dotypos'
											) }
										</Button>
									</span>
								) }
							&nbsp;
							<Button
								isPrimary
								onClick={ () => importProducts() }
							>
								<FontAwesomeIcon icon={ faCloudDownloadAlt } />
								&nbsp;
								{ __( 'Import all', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const ExportProductsModal = () => {
			return (
				<>
					{ isExportProductsModalOpen && (
						<Modal
							title={ __(
								'Export products to Dotypos?',
								'dotypos'
							) }
							onRequestClose={ () => closeExportProductsModal() }
						>
							<p>
								{ __(
									'This will create Woocommerce products in your Dotypos.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () => closeExportProductsModal() }
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => exportProducts() }
							>
								{ __( 'Export', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const OverwriteFromDotyposModal = () => {
			return (
				<>
					{ isOverwriteFromDotyposModalOpen && (
						<Modal
							title={ __(
								'Overwrite stocks from Dotypos?',
								'dotypos'
							) }
							onRequestClose={ () =>
								closeOverwriteFromDotyposModal()
							}
						>
							<p>
								{ __(
									'This will overwrite stock from Dotypos in your Woocommerce.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () =>
									closeOverwriteFromDotyposModal()
								}
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => overwriteFromDotypos() }
							>
								{ __( 'Overwrite', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const OverwriteFromWoocommerceModal = () => {
			return (
				<>
					{ isOverwriteFromWoocommerceModalOpen && (
						<Modal
							title={ __(
								'Overwrite stocks from Woocommerce?',
								'dotypos'
							) }
							onRequestClose={ () =>
								closeOverwriteFromWoocommerceModal()
							}
						>
							<p>
								{ __(
									'This will overwrite stock from Woocommerce in your Dotypos.',
									'dotypos'
								) }
							</p>
							<Button
								isSecondary
								onClick={ () =>
									closeOverwriteFromWoocommerceModal()
								}
							>
								{ __( 'Cancel', 'dotypos' ) }
							</Button>
							&nbsp;
							<Button
								isPrimary
								onClick={ () => overwriteFromWoocommerce() }
							>
								{ __( 'Overwrite', 'dotypos' ) }
							</Button>
						</Modal>
					) }
				</>
			);
		};

		const EnableProductsToggle = () => (
			<div>
				<ToggleControl
					label={ __( 'Enable synchronization', 'dotypos' ) }
					help={ __(
						'Your products are synced between Woocommerce and Dotypos',
						'dotypos'
					) }
					checked={ settings.product.enabled }
					disabled={ componentsInactive }
					onChange={ () => {
						updateAndSaveSetting(
							[ 'product.enabled' ],
							[ ! settings.product.enabled ]
						);
					} }
				/>
				{ settings.product.enabled && (
					<div>
						{ ! connectedWarehouse && (
							<>
								<Stepper
									steps={ stepsMovements }
									currentStep={ actualStepMovements }
									isPending={ false }
								/>
								<WarehousesModal />
							</>
						) }
						{ connectedWarehouse && (
							<>
								<SectionHeader
									title={
										__(
											'Connected to warehouse',
											'dotypos'
										) +
										' (' +
										settings.dotypos.warehouseId +
										')'
									}
								/>
								<h4>
									{ __( 'Products paired by', 'dotypos' ) +
										' ' }
									{ settings.product.movement
										.wcPairAttribute &&
									wcAttributeToString(settings.product.movement.wcPairAttribute) }
									&nbsp;
									<FontAwesomeIcon icon={ faExchangeAlt } />
									&nbsp;
									{ settings.product.movement
										.dotyposPairAttribute &&
										settings.product.movement.dotyposPairAttribute.toUpperCase() }
								</h4>
								<Button
									icon={
										<FontAwesomeIcon
											icon={ faUnlink }
											size="lg"
										/>
									}
									//isPrimary={ true }
									isDestructive={ true }
									onClick={ () => {
										resetPairing();
									} }
								>
									{ __( 'Reset pairing', 'dotypos' ) }
								</Button>
								<SectionHeader
									title={ __(
										'Settings products',
										'dotypos'
									) }
								/>
								<Section component={ false }>
									<ToggleControl
										label={ __(
											'Synchronize title',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize title of product when changed on both sides',
											'dotypos'
										) }
										checked={ settings.product.syncTitle }
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncTitle' ],
												[ ! settings.product.syncTitle ]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize price',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize price of product when changed on both sides',
											'dotypos'
										) }
										checked={ settings.product.syncPrice }
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncPrice' ],
												[ ! settings.product.syncPrice ]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize VAT',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize VAT of product when changed on both sides',
											'dotypos'
										) }
										checked={ settings.product.syncVat }
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncVat' ],
												[ ! settings.product.syncVat ]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize category',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize category of product when changed on both sides',
											'dotypos'
										) }
										checked={
											settings.product.syncCategory
										}
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncCategory' ],
												[
													! settings.product
														.syncCategory,
												]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize EAN',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize EAN of product when changed on both sides',
											'dotypos'
										) }
										checked={ settings.product.syncEAN }
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncEAN' ],
												[ ! settings.product.syncEAN ]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize note',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize note of product when changed on both sides',
											'dotypos'
										) }
										checked={ settings.product.syncNote }
										onChange={ () => {
											updateAndSaveSetting(
												[ 'product.syncNote' ],
												[ ! settings.product.syncNote ]
											);
										} }
									/>
								</Section>
								<SectionHeader
									title={ __(
										'Actions products',
										'dotypos'
									) }
								/>
								<Section component={ false }>
									<Button
										icon={
											importProductsClicked ? (
												<FontAwesomeIcon
													icon={ faCheckSquare }
													size="sm"
												/>
											) : (
												<FontAwesomeIcon
													icon={ faCloudDownloadAlt }
													size="sm"
												/>
											)
										}
										isPrimary
										disabled={ importProductsClicked }
										onClick={ () => {
											//Old way
											//openImportProductsModal();
											//The new wizard
											openImportProductsWizardModal();
										} }
										isSmall={ true }
									>
										{ importProductsClicked
											? __(
													'Import requested',
													'dotypos'
											  )
											: __(
													'Import from Dotypos',
													'dotypos'
											  ) }
									</Button>
									&nbsp;
									<Button
										icon={
											exportProductsClicked ? (
												<FontAwesomeIcon
													icon={ faCheckSquare }
													size="sm"
												/>
											) : (
												<FontAwesomeIcon
													icon={ faCloudUploadAlt }
													size="sm"
												/>
											)
										}
										isPrimary
										disabled={ exportProductsClicked }
										onClick={ () => {
											openExportProductsModal();
										} }
										isSmall={ true }
									>
										{ exportProductsClicked
											? __(
													'Export requested',
													'dotypos'
											  )
											: __(
													'Export to Dotypos',
													'dotypos'
											  ) }
									</Button>
									&nbsp;
									<Button
										icon={
											pairProductsClicked ? (
												<FontAwesomeIcon
													icon={ faCheckSquare }
													size="sm"
												/>
											) : (
												<FontAwesomeIcon
													icon={ faExchangeAlt }
													size="sm"
												/>
											)
										}
										isSecondary
										disabled={ pairProductsClicked }
										onClick={ () => {
											pairProducts();
										} }
										isSmall={ true }
									>
										{ pairProductsClicked
											? __(
													'Pairing requested',
													'dotypos'
											  )
											: __( 'Pair products', 'dotypos' ) }
									</Button>
									&nbsp;
									<Button
										icon={
											<FontAwesomeIcon
												icon={ faList }
												size="sm"
											/>
										}
										isSecondary
										href={
											'/wp-admin/edit.php?post_type=product'
										}
										isSmall={ true }
									>
										{ __( 'List products', 'dotypos' ) }
									</Button>
								</Section>
								<SectionHeader
									title={ __(
										'Settings movements',
										'dotypos'
									) }
								/>
								<Section component={ false }>
									<ToggleControl
										label={ __(
											'Synchronize Woocommerce to Dotypos',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize stock status when something happen on WC side',
											'dotypos'
										) }
										checked={
											settings.product.movement
												.syncToDotypos
										}
										onChange={ () => {
											updateAndSaveSetting(
												[
													'product.movement.syncToDotypos',
												],
												[
													! settings.product.movement
														.syncToDotypos,
												]
											);
										} }
									/>
									<ToggleControl
										label={ __(
											'Synchronize Dotypos to Woocommerce',
											'dotypos'
										) }
										help={ __(
											'Automatically synchronize stock status when something happen on Dotypos side',
											'dotypos'
										) }
										checked={
											settings.product.movement
												.syncFromDotypos
										}
										onChange={ () => {
											updateAndSaveSetting(
												[
													'product.movement.syncFromDotypos',
												],
												[
													! settings.product.movement
														.syncFromDotypos,
												]
											);
										} }
									/>
								</Section>
								<SectionHeader
									title={ __(
										'Actions movements',
										'dotypos'
									) }
								/>
								<Section component={ false }>
									<Button
										icon={
											overwriteFromDotyposClicked ? (
												<FontAwesomeIcon
													icon={ faCheckSquare }
													size="sm"
												/>
											) : (
												<FontAwesomeIcon
													icon={ faCloudDownloadAlt }
													size="sm"
												/>
											)
										}
										isPrimary
										disabled={ overwriteFromDotyposClicked }
										onClick={ () => {
											openOverwriteFromDotyposModal();
										} }
										isSmall={ true }
									>
										{ overwriteFromDotyposClicked
											? __(
													'Overwrite from Dotypos requested',
													'dotypos'
											  )
											: __(
													'Overwrite from Dotypos',
													'dotypos'
											  ) }
									</Button>
									&nbsp;
									<Button
										icon={
											overwriteFromWoocommerceClicked ? (
												<FontAwesomeIcon
													icon={ faCheckSquare }
													size="sm"
												/>
											) : (
												<FontAwesomeIcon
													icon={ faCloudUploadAlt }
													size="sm"
												/>
											)
										}
										isPrimary
										disabled={
											overwriteFromWoocommerceClicked
										}
										onClick={ () => {
											openOverwriteFromWoocommerceModal();
										} }
										isSmall={ true }
									>
										{ overwriteFromWoocommerceClicked
											? __(
													'Overwrite form Woocommerce requested',
													'dotypos'
											  )
											: __(
													'Overwrite from Woocommerce',
													'dotypos'
											  ) }
									</Button>
									<p>
										{ __(
											'Once overwrites the stock status for paired products from Dotypos to WooCommerce or from WooCommerce to Dotypos.',
											'dotypos'
										) }
									</p>
								</Section>
							</>
						) }
					</div>
				) }
				<ImportProductsModal />
				<ExportProductsModal />
				<OverwriteFromDotyposModal />
				<OverwriteFromWoocommerceModal />
				<ImportProductsWizardModal />
			</div>
		);

		return (
			<div>
				<Card className={ 'settingsCard' }>
					<CardHeader>
						<SectionHeader
							title={ __( 'General', 'dotypos' ) }
							className={ 'cardSectionHeader' }
						/>
					</CardHeader>
					<CardBody>
						{ /*__(
							'General settings for Dotypos Integration',
							'dotypos'
						) */ }
						{ licenceExpired && (
							<Notice
								status="error"
								onRemove={ () => {
									setState( ( state ) => ( {
										licenceExpired: false,
									} ) );
								} }
							>
								{ __(
									'Your licence expired please reconnect your Dotypos',
									'dotypos'
								) }
							</Notice>
						) }
						{ ( ! connected || activatedInSession ) && (
							<div>
								<Stepper
									steps={ steps }
									currentStep={ actualStep }
									isPending={ false }
								/>
							</div>
						) }
						{ ( connected || activatedInSession ) && (
							<div>
								<SectionHeader
									title={
										__( 'Connected to Cloud', 'dotypos' ) +
										' (' +
										settings.dotypos.cloudId +
										')'
									}
								/>
								<h5>
									{ __(
										'Licence verified with key',
										'dotypos'
									) +
										' (' +
										settings.dotypos.licenceKey +
										')' }
								</h5>
								&nbsp;
								<DisconnectModal />
								<Button
									icon={
										<FontAwesomeIcon
											icon={ faUnlink }
											size="lg"
										/>
									}
									//isPrimary={ true }
									isDestructive={ true }
									onClick={ () => {
										openDisconnectModal();
									} }
								>
									{ __(
										'Disconnect from Dotypos',
										'dotypos'
									) }
								</Button>
							</div>
						) }
					</CardBody>
				</Card>
				<Card className={ 'settingsCard' }>
					<CardHeader>
						<SectionHeader
							title={ __( 'Categories', 'dotypos' ) }
							className={ 'cardSectionHeader' }
						/>
					</CardHeader>
					<CardBody>
						{ /* __( 'Categories', 'dotypos' ) */ }
						<EnableCategoriesToggle />
					</CardBody>
				</Card>
				<Card className={ 'settingsCard' }>
					<CardHeader>
						<SectionHeader
							title={ __( 'Products & movements', 'dotypos' ) }
							className={ 'cardSectionHeader' }
						/>
					</CardHeader>
					<CardBody>
						{ /* __( 'Products & movements', 'dotypos' ) */ }
						<EnableProductsToggle />
					</CardBody>
				</Card>
			</div>
		);
	}
);

const DotyposConnectPage = withState( {
	token: null,
	cloudId: null,
	connected: false,
} )( ( { token, cloudId, setState, connected } ) => {
	const connectUrl = restApiUrl + 'dotypos/v1/connect';

	useEffect( () => {
		if (
			window.location.search.indexOf(
				'?page=wc-admin&path=%2Fdotypos-settings-connect'
			) !== -1
		) {
			require( './remove-admin.scss' );
			const htmlElement = document.getElementsByTagName( 'html' );
			htmlElement[ 0 ].classList.add( 'no-admin-bars' );

			if (
				getQueryVariable( 'cloudid' ) !== null &&
				getQueryVariable( 'token' ) !== null
			) {
				setState( ( state ) => ( {
					cloudId: getQueryVariable( 'cloudid' ),
					connected: true,
					token: getQueryVariable( 'token' ),
				} ) );
				dotyposFetch( connectUrl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify( {
						cloudId: getQueryVariable( 'cloudid' ),
						token: getQueryVariable( 'token' ),
					} ),
				} )
					.then( function ( res ) {
						return res.json();
					} )
					.then( function ( data ) {} );
			}
		}
	}, [] );

	function getQueryVariable( variable ) {
		const query = window.location.search.substring( 1 );
		const vars = query.split( '&' );
		for ( let i = 0; i < vars.length; i++ ) {
			const pair = vars[ i ].split( '=' );
			if ( decodeURIComponent( pair[ 0 ] ) == variable ) {
				return decodeURIComponent( pair[ 1 ] );
			}
		}
		console.log( 'Query variable %s not found', variable );
	}

	return (
		<>
			{ connected && (
				<>
					<Notice status="success" onRemove={ () => {} }>
						{ __(
							'Successfully connected to Dotypos Cloud with ID',
							'dotypos'
						) + ' ' }
						{ cloudId }
					</Notice>
					<p>{ __( 'Now you can close this window.', 'dotypos' ) }</p>
					<br />
					<Button
						icon={
							<FontAwesomeIcon icon={ faWindowClose } size="lg" />
						}
						isPrimary
						onClick={ () => {
							window.close();
						} }
					>
						{ __( 'Close window', 'dotypos' ) }
					</Button>
				</>
			) }
			{ ! connected && (
				<Notice status="error" onRemove={ () => {} }>
					{ __( 'Something went wrong. Try again.', 'dotypos' ) }
				</Notice>
			) }
		</>
	);
} );


addFilter( 'woocommerce_admin_pages_list', 'dotypos', ( pages ) => {
	pages.push( {
		container: DotyposSettings,
		path: '/dotypos-settings',
		breadcrumbs: [ __( 'Dotypos Settings', 'dotypos' ) ],
		navArgs: {
			id: 'dotypos-settings',
		},
	} );

	pages.push( {
		container: DotyposConnectPage,
		path: '/dotypos-settings-connect',
		breadcrumbs: [ __( 'Connect to Dotypos', 'dotypos' ) ],
		navArgs: {
			id: 'dotypos-settings-connect',
		},
	} );

	return pages;
} );
