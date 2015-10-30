import React, { PropTypes } from 'react';

export default class Spinner extends React.Component {

  static propTypes = {
    width: PropTypes.number.isRequired,
    height: PropTypes.number.isRequired,
    marginTop: PropTypes.number,
    marginLeft: PropTypes.number,
    assignClass: PropTypes.string
  };

  render() {
    const { assignClass } = this.props;
    const width = this.props.width || 20;
    const height = this.props.height || 20;
    const marginTop = this.props.marginTop || 0;
    const marginLeft = this.props.marginLeft || 0;

    const style = {
      width: width + 'px',
      height: height + 'px',
      marginTop: marginTop + 'px',
      marginLeft: marginLeft + 'px'
    };

    return <img src="/web/spinner.gif" className={assignClass ? assignClass : null} style={style} />;
  }
}
