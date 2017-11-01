import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Labels } from './Labels';
import { labelsSelector } from '../../../Selectors/nav';

@connect(state => ({
  labels: labelsSelector(state)
}))
export class LabelsContainer extends React.Component {

  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels } = this.props;
    const labelGroups = {};

    labels.forEach(label => {
      const name = label.get('label');
      const char = name && name.substr(0, 1).toUpperCase();

      if (!labelGroups[char]) {
        labelGroups[char] = [];
      }

      labelGroups[char].push(label.toJS());
    });

    return labels.size > 0 && <Labels labelGroups={Immutable.fromJS(labelGroups)} />;
  }
}
