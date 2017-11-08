import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Link } from 'react-scroll';

const getH1 = (html) => {
  const container = document.createElement('div');
  container.innerHTML = html;

  return Array.from(container.querySelectorAll('h1')).map(h1 => h1);
};

class TopicSummary extends React.Component {
  static propTypes = {
    content: PropTypes.string,
    fixed:   PropTypes.bool
  };

  constructor(props) {
    super(props);
    const h1s = getH1(this.props.content);
    this.state = {
      h1s,
      activeId: false
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      h1s: getH1(nextProps.content)
    });
  }

  handleSetActive = (to) => {
    window.history.replaceState(null, null, `#${to}`);
    this.setState({
      activeId: to
    });
  };

  render() {
    const { fixed } = this.props;
    const agentBar = window.document.getElementById('agent-bar');
    const offset = agentBar ? -50 : 0;
    return (
      <div className={classNames('topic-summary', { fixed })}>
        {this.state.h1s.length > 1 ?
          (<div>
            <h2><i className="fa fa-list" /> Contents</h2>
            <ul>
              {this.state.h1s.map((h1, index) => <li key={index}>
                <Link
                  href={`#${h1.id}`}
                  className={classNames({ active: this.state.activeId === h1.id })}
                  to={h1.id}
                  offset={offset}
                  spy
                  smooth
                  onSetActive={this.handleSetActive}
                >{h1.innerText}</Link>
              </li>)}
            </ul>
          </div>
          ) : null}
      </div>
    );
  }
}
export default TopicSummary;
