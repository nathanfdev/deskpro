import PropTypes from 'prop-types';
import React from 'react';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';

class Anchor extends React.Component {
  static propTypes = {
    text:   PropTypes.string,
    anchor: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      copied: false
    };
  }

  onClick = (e) => {
    e.preventDefault();
    if (copyTextToClipboard(`${window.location.href.replace(/#.*$/, '')}#${this.props.anchor}`)) {
      this.setState({
        copied: true
      });
      setTimeout(() => { this.setState({ copied: false }); }, 1000);
    }
    return false;
  };

  render() {
    return (
      <span className="anchor">
        {this.props.text}
        <a href={`#${this.props.anchor}`} onClick={this.onClick}>
          {this.state.copied ?
            <span>copied</span> :
            <i className="fa fa-anchor" /> }
        </a>
      </span>
    );
  }
}
export default Anchor;
