import PropTypes from 'prop-types';
import React from 'react';

export class AbstractCustomField extends React.Component {

  static propTypes = {
    config:        PropTypes.object,
    name:          PropTypes.string,
    value:         PropTypes.any,
    widgetOptions: PropTypes.object
  };

}
