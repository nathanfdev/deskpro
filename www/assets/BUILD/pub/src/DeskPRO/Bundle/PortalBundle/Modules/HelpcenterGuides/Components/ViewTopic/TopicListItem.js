import PropTypes from 'prop-types';
import React from 'react';
import Link from 'react-router/lib/Link';
import classNames from 'classnames';

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
    withSplash:       PropTypes.bool,
  };

  static defaultProps = {
    expandable: true,
    withSplash: false,
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

  handleClick = () => {
    const { topic } = this.props;
    this.props.grabTopicFromApi(topic.slug);
  };

  renderChildren = () => {
    const { topic, guideSlug, topicSlug, expanded, filter, filterTopic, grabTopicFromApi, withSplash } = this.props;
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
              withSplash={withSplash}
              expanded={(filter !== '' || child.slug === topicSlug || typeof Object.values(child.children)
                .find(c => c.slug === topicSlug ||  Object.values(c.children).find(cc => cc.slug === topicSlug)) !== 'undefined')}
            />
            )
          )}
      </ul>
    );
  };

  render() {
    const { topic, guideSlug } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    console.log(this.props.expanded);

    const prefix = this.getLevelPrefix();
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
        <Link
          className={classNames(`dp-po-guides-search-content-${prefix}link`)}
          to={`${baseUrl}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`}
          activeClassName="active"
          onClick={this.handleClick}
        >
          {topic.title}
        </Link>
        {this.renderChildren()}
      </li>
    );
  }
}
export default TopicListItem;
