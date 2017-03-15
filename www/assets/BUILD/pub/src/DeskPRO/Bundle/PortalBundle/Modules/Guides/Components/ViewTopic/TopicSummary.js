import React, { PropTypes } from 'react';
import classNames from 'classnames';

const getH1 = (html) => {
  const container = document.createElement('div');
  container.innerHTML = html;

  return Array.from(container.querySelectorAll('h1')).map(h1 => h1);
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
    const h1s = getH1(this.props.content);
    this.state = {
      fixed:    false,
      agentBar: !!agentBar,
      h1s
    };
  }

  componentDidMount() {
    if (this.state.h1s.length) {
      window.addEventListener('scroll', this.handleScroll);
    }
    this.offsetTop = this.summary.offsetTop;
  }

  componentWillReceiveProps(nextProps) {
    const h1s = getH1(nextProps.content);
    this.setState({
      h1s
    });
    if (h1s.length) {
      window.addEventListener('scroll', this.handleScroll);
    } else {
      window.removeEventListener('scroll', this.handleScroll);
    }
  }

  componentWillUnmount() {
    if (this.state.h1s.length) {
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
    const { fixed } = this.state;
    const style = {
      top: this.agentBarHeight + 20
    };
    return (
      <div className={classNames('topic-summary', { fixed })} ref={(c) => { this.summary = c; }} style={style}>
        {this.state.h1s.length ?
          (<div>
            <h2><i className="fa fa-list" /> Contents</h2>
            <ul>
              {this.state.h1s.map((h1, index) => <li key={index}>
                <a href={`#${h1.id}`}>
                  {h1.innerText}
                </a>
              </li>)}
            </ul>
          </div>
          ) : null}
      </div>
    );
  }
}
export default TopicSummary;
