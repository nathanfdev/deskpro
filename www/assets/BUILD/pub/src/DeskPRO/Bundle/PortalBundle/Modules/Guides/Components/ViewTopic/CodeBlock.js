import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';

class CodeBlock extends React.Component {
  static propTypes = {
    text: PropTypes.string,
    html: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      copied: false
    };
  }

  onClick = (e) => {
    e.preventDefault();
    if (copyTextToClipboard(this.props.text)) {
      this.setState({
        copied: true
      });
      setTimeout(() => { this.setState({ copied: false }); }, 1000);
    }
    return false;
  };

  render() {
    return (
      <span className="code-block">
        <div dangerouslySetInnerHTML={{ __html: this.props.html }} />
        <a className={classNames('code-copy', { copied: this.state.copied })} onClick={this.onClick}>
          {this.state.copied ?
            <span><i className="fa fa-check" /> copied</span> :
            <span><i className="fa fa-clipboard" /> copy</span> }
        </a>
      </span>
    );
  }
}
export default CodeBlock;
