import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ChoiceMenu } from '../../Form/ChoiceMenu';
import { CheckboxOption } from '../../Form/CheckboxOption';
import { connect } from 'react-redux';

@connect()
export class MultipleActionChoiceContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    item:              PropTypes.object.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  handleClick(param, value, values) {
    const { setParams, dispatch, resetSingleAction } = this.props;
    const index = values.indexOf(value);
    if (index > -1) {
      values.splice(index, 1);
    } else {
      values.push(value);
    }
    if (values.length > 0) {
      dispatch(setParams({ [param]: values }));
    } else {
      dispatch(resetSingleAction(param));
    }
  }

  renderCheckboxOption(option, index) {
    const { currentParams, item } = this.props;
    const values = currentParams && currentParams.get(item.param) ? currentParams.get(item.param).toArray() : [];

    return (
      <CheckboxOption key={index}
        values={values}
        label={option.label}
        value={option.value}
        onClick={this.handleClick.bind(this, item.param, option.value, values)}
      />
    );
  }

  render() {
    const { item } = this.props;

    return (
      <ChoiceMenu>
        <ul>
          {item.options.map((option, index) => this.renderCheckboxOption(option, index))}
        </ul>
      </ChoiceMenu>
    );
  }
}
