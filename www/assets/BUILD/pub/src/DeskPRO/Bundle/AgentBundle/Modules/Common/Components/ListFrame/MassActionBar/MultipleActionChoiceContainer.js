import React, { Component, PropTypes } from 'react';
import { ChoiceMenu } from '../../Form/ChoiceMenu';
import { CheckboxOption } from '../../Form/CheckboxOption';

import { connect } from 'react-redux';
@connect()
export class MultipleActionChoiceContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.array.isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object
  };

  handleClick(param) {
    const { setParams, dispatch, currentParams, resetSingleAction } = this.props;
    if (currentParams && currentParams.get(param)) {
      dispatch(resetSingleAction(param));
    } else {
      dispatch(setParams({ [param]: true }));
    }
  }

  renderCheckboxOption(option, index) {
    const {currentParams } = this.props;
    const values = currentParams && currentParams.get(option.param) ? [option.param] : [];

    return (
      <CheckboxOption key={index}
                      label={option.label}
                      values={values}
                      value={option.param}
                      onClick={this.handleClick.bind(this, option.param)}/>
    );
  }

  render() {
    const {options } = this.props;

    return (
      <ChoiceMenu>
        <ul>
          {options.map((option, index) => this.renderCheckboxOption(option, index))}
        </ul>
      </ChoiceMenu>
    );
  }
}