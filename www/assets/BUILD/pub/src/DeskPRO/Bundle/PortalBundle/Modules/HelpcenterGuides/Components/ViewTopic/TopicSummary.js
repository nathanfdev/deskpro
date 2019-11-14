import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Link } from 'react-scroll';

const getHeading = (html) => {
  const container = document.createElement('div');
  container.innerHTML = html;

  return Array.from(container.querySelectorAll('h1')).map(h1 => h1);
};

class TopicSummary extends React.Component {
  static propTypes = {
    content:   PropTypes.string,
    fixed:     PropTypes.bool,
    className: PropTypes.string,
  };

  constructor(props) {
    super(props);
    const h1s = getHeading(this.props.content);
    this.state = {
      h1s,
      activeId: false
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      h1s: getHeading(nextProps.content)
    });
  }

  handleSetActive = (to) => {
    window.history.replaceState(null, null, `#${to}`);
    this.setState({
      activeId: to
    });
  };

  render() {
    const { fixed, className } = this.props;
    const agentBar = window.document.getElementById('agent-bar');
    const offset = agentBar ? -50 : 0;
    return (
      <div className={classNames('dp-po-guides-contents', className, { fixed })}>
        {this.state.h1s.length > 1 ?
          (<div>
            <h3 className="dp-po-guides-contents-title">Contents</h3>
            <ul className="dp-po-guides-contents-list">
              {this.state.h1s.map((h1, index) => <li className="dp-po-guides-contents-item" key={index}>
                <Link
                  href={`#${h1.id}`}
                  className={classNames('dp-po-guides-contents-link', { active: this.state.activeId === h1.id })}
                  to={h1.id}
                  offset={offset}
                  spy
                  smooth
                  onSetActive={this.handleSetActive}
                ><i className="dp-po-icon fal fa-angle-right" />{h1.innerText}</Link>
              </li>)}
            </ul>
          </div>
          ) : null}
      </div>
    );
  }
}
export default TopicSummary;
