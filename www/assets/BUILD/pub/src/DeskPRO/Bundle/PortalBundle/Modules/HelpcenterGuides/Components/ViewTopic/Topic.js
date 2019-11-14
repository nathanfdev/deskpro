import PropTypes from 'prop-types';
import React from 'react';
import Link from 'react-router/lib/Link';
import classNames from 'classnames';

class Topic extends React.Component {
  static propTypes = {
    topic:      PropTypes.object,
    guideSlug:  PropTypes.string,
    path:       PropTypes.string,
    clickable:  PropTypes.bool,
    expandable: PropTypes.bool,
    level:      PropTypes.number,
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
    const { topic, guideSlug, expandable, level } = this.props;
    if (!Object.values(topic.children).length) {
      return null;
    }
    const prefix = this.getLevelPrefix(1);
    return (
      <ul
        className={classNames(`dp-po-guides-search-content-${prefix}list`, {
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
              guideSlug={guideSlug}
              clickable={parseInt(child.no_content, 10) === 0}
              path={this.props.path}
              level={level + 1}
            />
          )
        )}
      </ul>
    );
  };

  getLevelPrefix = (delta = 0) => {
    const { level } = this.props;
    switch (level + delta) {
      case 0:
        return '';
      case 1:
        return 'sup';
      case 2:
        return 'sub';
      case 3:
        return 'under';
      default:
        return 'under';
    }
  };

  toggleChildren = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  render() {
    const { topic, guideSlug, clickable } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const prefix = this.getLevelPrefix();
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
        {clickable ?
          <Link to={`${baseUrl}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`} className={`dp-po-guides-search-content-${prefix}link`} activeClassName="active" onClick={this.toggleChildren}>
            {topic.title}
          </Link> :
          <a className={`dp-po-guides-search-content-${prefix}link`}>{topic.title}</a>
        }
        {this.getChildren()}
      </li>
    );
  }
}
export default Topic;
