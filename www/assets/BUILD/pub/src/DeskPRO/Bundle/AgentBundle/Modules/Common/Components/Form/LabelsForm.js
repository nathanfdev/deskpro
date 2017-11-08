import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { QuickFilter } from './QuickFilter';
import classNames from 'classnames';

export class LabelsForm extends Component {

  static propTypes = {
    changeMode:     PropTypes.func.isRequired,
    selectLabel:    PropTypes.func.isRequired,
    deselectLabel:  PropTypes.func.isRequired,
    selectedLabels: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
    allLabels:      PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
    matchMode:      PropTypes.bool,
    params:         PropTypes.object
  };

  render() {
    const { params, changeMode, allLabels, selectLabel, selectedLabels, deselectLabel, matchMode } = this.props;
    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
        {matchMode && <LabelsMatchingMode mode={params.get('mode')} changeMode={changeMode} />}

        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <SelectedLabels selectedLabels={selectedLabels} deselectLabel={deselectLabel} />
            <hr />
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw-label-long-list">
                <QuickFilter />
                <LabelsCollection allLabels={allLabels} selectLabel={selectLabel} />
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export class LabelsMatchingMode extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    mode:       PropTypes.string
  };

  render() {
    const { mode, changeMode } = this.props;
    return (
      <div className="dpw-navigation-dropdown-header dpw-navigation-dropdown-label-matching">
        <div>
          <AllLabelsMatchingMode mode={mode} changeMode={changeMode} />
        </div>
        <div>
          <AnyLabelMatchingMode mode={mode} changeMode={changeMode} />
        </div>
      </div>
    );
  }
}

export class AllLabelsMatchingMode extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    mode:       PropTypes.string
  };

  handleClick = () => {
    const { changeMode } = this.props;
    changeMode('all');
  };

  render() {
    const classes = classNames('dpwd-radio-button', { active: this.props.mode === 'all' });
    return (
      <span className={classes} onClick={this.handleClick}>
        <span className="dpwd-radio-button-disc" />
        <span className="radio-button-title">Match all selected labels</span>
      </span>
    );
  }
}

export class AnyLabelMatchingMode extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    mode:       PropTypes.string
  };

  handleClick = () => {
    const { changeMode } = this.props;
    changeMode('all');
  };

  render() {
    const classes = classNames('dpwd-radio-button', { active: this.props.mode === 'any' });
    return (
      <span className={classes} onClick={this.handleClick}>
        <span className="dpwd-radio-button-disc" />
        <span className="radio-button-title">Match any selected label</span>
      </span>
    );
  }
}

export class SelectedLabels extends Component {

  static propTypes = {
    selectedLabels: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
    deselectLabel:  PropTypes.func.isRequired
  };

  renderLabel = (label, index) => {
    const { deselectLabel } = this.props;
    return (
      <li key={index} onClick={deselectLabel.bind(this, label)}>
        <a href="#" className="dpw-item-label">
          <i className="fa fa-times" /> {label}
        </a>
      </li>
    );
  };

  render() {
    const { selectedLabels } = this.props;
    if (selectedLabels) {
      return (
        <div className="dpw-navigation-dropdown-panel-content-full">
          <div className="dpw-label-pile">
            <ul className="dpw-label-list">
              {selectedLabels.map(
                (item, index) => this.renderLabel(item, index)
              )}
            </ul>
          </div>
        </div>
      );
    }
    return (
      <div className="dpw-navigation-dropdown-panel-content-full">
        <div className="dpw-label-pile"></div>
      </div>
    );
  }
}

export class LabelsCollection extends Component {

  static propTypes = {
    selectLabel: PropTypes.func.isRequired,
    allLabels:   PropTypes.oneOfType([PropTypes.array, PropTypes.object])
  };

  renderLabel = (item, index) => {
    const { selectLabel } = this.props;
    return (<li key={index} onClick={selectLabel.bind(this, item.get('label'))}>
      <a href="#" className="dpw-item-label">
        {item.get('label')}
      </a>
    </li>);
  };

  render() {
    const { allLabels } = this.props;

    return (
      <div className="dpw-label-list-content">
        <ul className="dpw-label-list">
          {allLabels.map(
            (item, index) => this.renderLabel(item, index))}
        </ul>
      </div>
    );
  }
}
