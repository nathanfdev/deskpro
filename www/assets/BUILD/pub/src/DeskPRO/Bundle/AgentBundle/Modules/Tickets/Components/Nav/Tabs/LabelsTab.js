import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { LabelsDictionary } from '../../../../Common/Components/NavFrame';

export class LabelsTab extends Component {

  static propTypes = {
    labels:       PropTypes.object.isRequired,
    onLabelClick: PropTypes.object.isRequired
  };

  render() {
    return <LabelsDictionary labels={this.props.labels} onClick={this.props.onClick} />;
  }
}
