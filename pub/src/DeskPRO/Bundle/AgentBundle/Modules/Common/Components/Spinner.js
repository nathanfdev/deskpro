import React, { PropTypes } from 'react';

export default class Spinner extends React.Component {

  static propTypes = {
    width: PropTypes.number.isRequired,
    height: PropTypes.number.isRequired,
    assignClass: PropTypes.string
  };

  render() {
    const {assignClass, width, height} = this.props;
    return <img src="/web/spinner.gif" className={assignClass ? assignClass : null} style={{width: width + 'px', height: height + 'px'}}/>;
  }
}
