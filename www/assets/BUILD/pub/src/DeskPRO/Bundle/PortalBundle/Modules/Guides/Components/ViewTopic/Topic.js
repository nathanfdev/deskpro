import PropTypes from 'prop-types';
import React from 'react';
import Link from 'react-router/lib/Link';
import classNames from 'classnames';

class Topic extends React.Component {
  static propTypes = {
    topic:      PropTypes.object,
    locale:     PropTypes.string,
    guideSlug:  PropTypes.string,
    path:       PropTypes.string,
    clickable:  PropTypes.bool,
    expandable: PropTypes.bool
  };

  static defaultProps = {
    expandable: true,
    clickable:  true,
  };

  constructor(props) {
    super(props);
    let expanded = !this.props.expandable;
    if (this.props.expandable && window.location.href.match(props.topic.slug)) {
      expanded = true;
    }
    this.state = {
      expanded
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      expanded: (this.props.expandable && nextProps.path.match(this.props.topic.slug))
    });
  }

  getChildren = () => {
    const { topic, locale, guideSlug, expandable } = this.props;
    if (!Object.values(topic.children).length) {
      return null;
    }
    return (
      <ul
        className={classNames({
          hidden: expandable && !this.state.expanded,
          expandable
        })}
      >
        {Object.values(topic.children)
          .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
          .map(child => (
            <Topic
              key={child.slug}
              topic={child}
              locale={locale}
              guideSlug={guideSlug}
              clickable={parseInt(child.no_content, 10) === 0}
              path={this.props.path}
            />
          )
        )}
      </ul>
    );
  };

  toggleChildren = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  render() {
    const { topic, locale, guideSlug, clickable } = this.props;
    let { expandable } = this.props;
    if (!Object.values(topic.children).length) {
      expandable = false;
    }
    return (
      <li className="topic-item" key={topic.slug}>
        {clickable ?
          <Link to={`${locale}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`} activeClassName="active" onClick={this.toggleChildren}>
            {topic.title}
            {expandable ? <i
              className={classNames(
              'fa pull-right',
              { 'fa-caret-right': !this.state.expanded, 'fa-caret-down': this.state.expanded })}
            /> : null }
          </Link> :
          <a>{topic.title}</a>
        }
        {this.getChildren()}
      </li>
    );
  }
}
export default Topic;
