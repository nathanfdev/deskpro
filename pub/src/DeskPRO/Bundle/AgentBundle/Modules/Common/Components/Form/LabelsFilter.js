import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';
import classNames from 'classnames';

export class LabelsFilter extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    allLabels: PropTypes.array.isRequired,
    params: PropTypes.object
  };

  render() {
    const {params, changeMode, allLabels} = this.props;
    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
        <LabelsMatchingMode mode={params.get('mode')} changeMode={changeMode}/>

        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <SelectedLabels/>
            <hr/>
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw-label-long-list">
                <QuickFilter />
                <LabelsCollection allLabels={allLabels}/>
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
    mode: PropTypes.string
  };

  render() {
    const {mode, changeMode} = this.props;
    return (
      <div className="dpw-navigation-dropdown-header dpw-navigation-dropdown-label-matching">
        <div>
          <AllLabelsMatchingMode mode={mode} changeMode={changeMode}/>
        </div>
        <div>
          <AnyLabelMatchingMode mode={mode} changeMode={changeMode}/>
        </div>
      </div>
    );
  }
}

export class AllLabelsMatchingMode extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    mode: PropTypes.string
  };

  render() {
    const { changeMode } = this.props;
    const classes = classNames('dpwd-radio-button', { 'active': this.props.mode !== 'any' });
    return (
      <span className={classes} onClick={changeMode.bind(this, 'all')}>
        <span className="dpwd-radio-button-disc"></span>
        <span className="radio-button-title">Match all selected labels</span>
      </span>
    );
  }
}


export class AnyLabelMatchingMode extends Component {

  static propTypes = {
    changeMode: PropTypes.func.isRequired,
    mode: PropTypes.string
  };

  render() {
    const { changeMode } = this.props;
    const classes = classNames('dpwd-radio-button', { 'active': this.props.mode === 'any' });
    return (
      <span className={classes} onClick={changeMode.bind(this, 'any')}>
        <span className="dpwd-radio-button-disc"></span>
        <span className="radio-button-title">Match any selected label</span>
      </span>
    );
  }
}

export class SelectedLabels extends Component {
  render() {
    return (
      <div className="dpw-navigation-dropdown-panel-content-full">
        <div className="dpw-label-pile">
          <ul className="dpw-label-list">
            <li><a href="#" className="dpw-item-label"><i className="fa fa-times"></i> diditwork</a></li>
            <li><a href="#" className="dpw-item-label"><i className="fa fa-times"></i> android</a></li>
            <li><a href="#" className="dpw-item-label"><i className="fa fa-times"></i> windows</a></li>
            <li><a href="#" className="dpw-item-label"><i className="fa fa-times"></i> mac</a></li>
          </ul>
        </div>
      </div>
    );
  }
}

export class LabelsCollection extends Component {

  static propTypes = {
    allLabels: PropTypes.array.isRequired
  };

  render() {
    const {allLabels} = this.props;
    return (
      <div className="dpw-label-list-content">
        <ul className="dpw-label-list">
          {allLabels.map(
            (item, index) =>
              <li key={index}><a href="#" className="dpw-item-label">{item}</a></li>
          )}
        </ul>
      </div>
    );
  }
}