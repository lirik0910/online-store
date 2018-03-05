/**
 * Base module of manager users container
 * @module ProductContainers
 * @author Ihor Bielchenko
 * @requires react
 * @requires react#Component
 * @requires redux#bindActionCreators
 * @requires react-redux#connect
 */

import App from '../../App.js';
import React, { Component } from 'react';

import Paper from 'material-ui/Paper';
import { Link } from 'react-router-dom';
import ContextItem from './ContextItem/ContextItem.jsx';
import DialogError from '../DialogError/DialogError.jsx';
import DialogDelete from '../DialogDelete/DialogDelete.jsx';

import styles from './styles.js';
import PropTypes from 'prop-types';
import { withStyles } from 'material-ui/styles';

/**
 * Users base container
 * @extends Component
 */
class PaperPages extends Component {

	/**
	 * Init default props
	 * @type {Object} 
	 * @inner
	 * @property {Object} classes Material defult classes collection 
	 */
	static defaultProps = {
		classes: PropTypes.object.isRequired,
	}

	state = {
		a: '',
		ready: true,
		contexts: [],
		deleteItem: {},
		deleteDialog: false,
		resultDialog: false,
		resultDialogTitle: '',
		resultDialogMessage: '',
	}

	/**
	 * Invoked just before mounting occurs
	 * @fires componentWillMount
	 */
	componentWillMount() {
		this.contextsGetDataRequest();
	}

	/**
	 * Request for getting contexts array
	 * @param {Function} callback
	 */
	contextsGetDataRequest(callback = () => {}) {
		App.api({
			type: 'GET',
			name: 'all',
			model: 'context',
			success: (r) => {
				r = JSON.parse(r.response);
				if (r) {
					this.setState({ contexts: r }, () => callback(r));
				}
			}
		});
	}

	pageDeleteRequest(callback = () => {}) {
		let { deleteItem } = this.state;

		App.api({
			name: 'one',
			model: 'page',
			type: 'DELETE',
			resource: deleteItem.id,
			success: (r) => {
				r = JSON.parse(r.response);
				if (r) {
					this.setState({ 
						ready: false,
						deleteItem: {},
						deleteDialog: false,
					}, () => this.contextsGetDataRequest(() => {
						this.setState({ ready: true })
					}));
				}
			},
			error: (r) => {
				r = JSON.parse(r.response);
				if (r.message) {
					this.setState({ 
						deleteItem: {},
						deleteDialog: false,
						resultDialog: true,
						resultDialogTitle: 'Error',
						resultDialogMessage: r.message
					});
				}
			}
		});
	}

	/**
	 * Render component
	 * @return {Object} jsx object
	 */
	render() {
		let { classes } = this.props;
		let { 
			a,
			ready,
			contexts,
			deleteDialog,
			resultDialog,
			resultDialogTitle,
			resultDialogMessage
		} = this.state;

		return <Paper className={classes.paper}>
			{ready && contexts.map((item, i) => {
				return <ContextItem
					key={i}
					context={item}
					onDeleteButtonClicked={page => this.setState({
						deleteItem: page,
						deleteDialog: true
					})}
					onCreateChildPageClicked={page => {
						this.setState({
							a: App.name() +'/pages/create?parent_id='+ page.id +'&context_id='+ page.context_id +'&link='+ page.link
						}, () => {
							var el = document.getElementById('change-page');
							if (el) {
								el.click();
							}
						});
					}} />
			})}

			{resultDialog === true && <DialogError 
				title={resultDialogTitle}
				defaultValue={resultDialog}
				message={resultDialogMessage}
				onDialogClosed={() => this.setState({
					resultDialog: false
				})} />}

			{deleteDialog === true && <DialogDelete
				defaultValue={deleteDialog}
				onDialogClosed={() => this.setState({
					deleteDialog: false,
				})}
				onDialogConfirmed={() => this.pageDeleteRequest()} />}

			<Link to={a}
				id="change-page"
				style={{display: 'none'}}></Link>
		</Paper>
	}
}

export default withStyles(styles)(PaperPages);