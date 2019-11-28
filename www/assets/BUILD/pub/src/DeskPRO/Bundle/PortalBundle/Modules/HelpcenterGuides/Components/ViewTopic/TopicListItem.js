import PropTypes from 'prop-types';
import React from 'react';
import { Link } from 'react-scroll';
import classNames from 'classnames';
import browserHistory from 'react-router/lib/browserHistory';

class TopicListItem extends React.Component {
  static propTypes = {
    topic:            PropTypes.object,
    guideSlug:        PropTypes.string,
    topicSlug:        PropTypes.string,
    path:             PropTypes.string,
    filter:           PropTypes.string,
    expanded:         PropTypes.bool,
    grabTopicFromApi: PropTypes.func,
    filterTopic:      PropTypes.func,
  };

  static defaultProps = {
    expandable: true,
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

  renderChildren = () => {
    const { topic, guideSlug, topicSlug, expanded, filter, filterTopic, grabTopicFromApi } = this.props;
    if (!Object.values(topic.children).length) {
      return null;
    }
    const prefix = this.getLevelPrefix(1);
    const style = {};
    if (!expanded) {
      style.display = 'none';
    }
    return (
      <ul
        className={classNames(`dp-po-guides-search-content-${prefix}list`)}
        style={style}
      >
        {Object.values(topic.children)
          .filter(t => filterTopic(t))
          .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
          .map(child => (
            <TopicListItem
              key={child.slug}
              topic={child}
              guideSlug={guideSlug}
              topicSlug={topicSlug}
              path={this.props.path}
              grabTopicFromApi={grabTopicFromApi}
              filter={filter}
              filterTopic={filterTopic}
              expanded={(filter !== '' || child.slug === topicSlug || Object.values(child.children)
                .find(c => c.slug === topicSlug || Object.values(c.children).find(cc => cc.slug === topicSlug)))}
            />
            )
          )}
      </ul>
    );
  };

  render() {
    const { topic, guideSlug, topicSlug } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const prefix = this.getLevelPrefix();
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
        <Link
          className={classNames(`dp-po-guides-search-content-${prefix}link`, { active: topic.slug === topicSlug })}
          activeClass="active"
          href={`${baseUrl}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`}
          to={`topic_${topic.slug}`}
          offset={-129}
          spy
          isDynamic
          onClick={this.handleClick}
          onSetActive={this.handleSetActive}
        >
          {topic.title}
        </Link>
        {this.renderChildren()}
      </li>
    );
  }
}
export default TopicListItem;
