import PropTypes from 'prop-types';
import React from 'react';
import { Link } from 'react-scroll';
import classNames from 'classnames';
import browserHistory from 'react-router/lib/browserHistory';

class TopicListItem extends React.Component {
  static propTypes = {
    topic:            PropTypes.object,
    guideSlug:        PropTypes.string,
    path:             PropTypes.string,
    expandable:       PropTypes.bool,
    grabTopicFromApi: PropTypes.func,
  };

  static defaultProps = {
    expandable: true,
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
    const { topic, guideSlug, expandable, grabTopicFromApi } = this.props;
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
            <TopicListItem
              key={child.slug}
              topic={child}
              guideSlug={guideSlug}
              path={this.props.path}
              grabTopicFromApi={grabTopicFromApi}
            />
          )
        )}
      </ul>
    );
  };

  getLevelPrefix = (delta = 0) => {
    const { topic } = this.props;
    switch (topic.depth + delta) {
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

  handleClick = (e) => {
    e.preventDefault();
    const { topic } = this.props;
    this.props.grabTopicFromApi(topic.slug);
    this.toggleChildren();
  };

  handleSetActive = () => {
    const { topic, guideSlug } = this.props;
    this.props.grabTopicFromApi(topic.slug);

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    browserHistory.push(`${baseUrl}/guides/${guideSlug}/${topic.slug}`);
  };

  toggleChildren = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  render() {
    const { topic, guideSlug } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const prefix = this.getLevelPrefix();
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
        <Link
          className={`dp-po-guides-search-content-${prefix}link`}
          activeClass="active"
          href={`${baseUrl}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`}
          to={`topic_${topic.id}`}
          offset={-178}
          spy
          smooth
          isDynamic
          onClick={this.handleClick}
          onSetActive={this.handleSetActive}
        >
          {topic.title}
        </Link>
        {this.getChildren()}
      </li>
    );
  }
}
export default TopicListItem;
