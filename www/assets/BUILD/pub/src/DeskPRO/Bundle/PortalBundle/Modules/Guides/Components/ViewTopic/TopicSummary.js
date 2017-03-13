import React, { PropTypes } from 'react';
import classNames from 'classnames';

const getH1 = (html) => {
  const container = document.createElement('div');
  container.innerHTML = html;

  return Array.from(container.querySelectorAll('h1')).map((h1, index) => <li key={index}>
    <a href={`#${h1.id}`}>
      {h1.innerText}
    </a>
  </li>);
};

class TopicSummary extends React.Component {
  static propTypes = {
    content: PropTypes.string
  };

  constructor(props) {
    super(props);
    const agentBar = window.document.getElementById('agent-bar');
    this.offsetTop = 0;
    this.agentBarHeight = 0;
    if (agentBar) {
      this.agentBarHeight = agentBar.offsetHeight;
    }
    this.state = {
      fixed:    false,
      agentBar: !!agentBar
    };
    this.h1s = getH1(this.props.content);
  }

  componentDidMount() {
    if (this.h1s.length) {
      window.addEventListener('scroll', this.handleScroll);
      this.offsetTop = this.summary.offsetTop;
    }
  }

  componentWillUnmount() {
    if (this.h1s.length) {
      window.removeEventListener('scroll', this.handleScroll);
    }
  }

  handleScroll = (event) => {
    const topOffset = this.offsetTop - 20 - this.agentBarHeight;
    if (event.srcElement.body.scrollTop > topOffset && !this.state.fixed) {
      this.setState({
        fixed: true
      });
    } else if (event.srcElement.body.scrollTop < topOffset && this.state.fixed) {
      this.setState({
        fixed: false
      });
    }
  };

  render() {
    if (!this.h1s.length) {
      return null;
    }
    const { fixed } = this.state;
    const style = {
      top: this.agentBarHeight + 20
    };
    return (
      <div className={classNames('topic-summary', { fixed })} ref={(c) => { this.summary = c; }} style={style}>
        <h2><i className="fa fa-list" /> Contents</h2>
        <ul>
          {this.h1s}
        </ul>
      </div>
    );
  }
}
export default TopicSummary;
