import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Labels } from './Labels';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';

@connect(state => ({
  labels: allSelectorFactory('TaskLabel')(state)
}))
export class LabelsContainer extends React.Component {

  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels = [] } = this.props;
    const labelGroups = {};

    labels.forEach(label => {
      const name = label.get('label');
      const char = name && name.substr(0, 1).toUpperCase();

      if (!labelGroups[char]) {
        labelGroups[char] = [];
      }

      labelGroups[char].push(label.toJS());
    });

    return <Labels labelGroups={Immutable.fromJS(labelGroups)} />;
  }
}
