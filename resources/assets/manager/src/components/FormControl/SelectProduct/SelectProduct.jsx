/**
 * Select product module
 * @module Selectproduct
 * @author Ihor Bielchenko
 * @requires react
 */

import App from '../../../App.js';
import React, { Component } from 'react';
import Select from 'material-ui/Select';
import { MenuItem } from 'material-ui/Menu';
import { FormControl } from 'material-ui/Form';
import Input, { InputLabel } from 'material-ui/Input';

import styles from './styles.js';
import PropTypes from 'prop-types';
import { withStyles } from 'material-ui/styles';


/**
 * Component for selecting product
 * @extends Component
 */
class SelectProduct extends Component {

	/**
	 * Init default props
	 * @type {Object} 
	 * @inner
	 * @property {Object} classes Material defult classes collection 
	 */
	static defaultProps = {
		defaultValue: 0,
		required: false,
		title: 'Select product',
		inputID: 'select-product',
		onDataLoaded: () => {},
		onItemSelected: () => {},
		classes: PropTypes.object.isRequired,
		category_id: 1,
		products: [],
		contexts:[]
	}

	/**
	 * State object of component
	 * @type {Object} 
	 * @inner
	 * @property {Array} data
	 * @property {String} currentID 
	 */
	state = {
		data: [], 
		value: 0,
	}

	/**
	 * Invoked just before mounting occurs
	 * @fires componentWillMount
	 */
	componentWillMount() {
		this.setState({ 
			value: this.props.defaultValue
		});
	}

	/**
	 * Change select fields
	 * @fires click
	 * @param {Object} e
	 */
	handleChangeSelect = e => {
		var target = e.target;
		this.setState({ value: target.value }, () => {
			this.props.onItemSelected(target.value);
		});
	}

	/**
	 * Render component
	 * @return {Object} jsx object
	 */
	render() {
		let { data, value } = this.state;
		let { classes, inputID, title, required, products, contexts } = this.props;
		return <FormControl className={classes.formControl}>
			<InputLabel htmlFor={inputID}>
				{title}
			</InputLabel>
			
			<Select
				required={required}
				value={value}
				onChange={this.handleChangeSelect}
				input={<Input name="product_id" id={inputID} />}
				className="product-select__container"
				style={{
					overflowY: 'scroll'
				}}>

				<MenuItem value={0}>
					<em>{this.props.lexicon['labelNoneSelected']}</em>
				</MenuItem>

				{products.map((item, i) => {
					return <MenuItem 
						key={i}
						value={typeof item.product_id === 'undefined' ?
							item.id :
							item.product_id}>

						{item.title} / {contexts[item.context_id]} / {item.price}
					</MenuItem>
				})}
			</Select>
		</FormControl>
	}
}

export default withStyles(styles)(SelectProduct);