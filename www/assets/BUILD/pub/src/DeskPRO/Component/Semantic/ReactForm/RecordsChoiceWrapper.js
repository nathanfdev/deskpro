import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';

class RecordsChoiceWrapper extends React.Component {

  static propTypes = {
    records:   PropTypes.object,
    labelProp: PropTypes.string,
    children:  PropTypes.node
  };

  static defaultProps = {
    labelProp: 'name'
  };

  render() {
    const { children, labelProp, records = Immutable.fromJS([]) } = this.props;
    const choices = records.map(queue => ({
      value: queue.get('id'),
      label: queue.get(labelProp)
    })).toArray();

    return React.cloneElement(children, { ...this.props, ...children.props, choices });
  }
}

export default RecordsChoiceWrapper;
