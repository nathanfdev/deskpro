import React, { PropTypes } from 'react';
import { Link } from 'react-router';

class TopicList extends React.Component {
  static propTypes = {
    topics:    PropTypes.object,
    locale:    PropTypes.string,
    guideSlug: PropTypes.string
  };

  getChildren = (topic) => {
    if (!Object.values(topic.children).length) {
      return null;
    }
    const { locale, guideSlug } = this.props;
    return (
      <ul>
        {Object.values(topic.children).map(child => (
          <li className="topic-item" key={child.slug}>
            <Link to={`/${locale}/guides/${guideSlug}/${child.slug}`} activeClassName="active">
              {child.title}
            </Link>
            {this.getChildren(child)}
          </li>
        ))}
      </ul>
    );
  };

  render() {
    const { locale, topics, guideSlug } = this.props;
    return (<ul>
      {Object.values(topics).map(topic => (
        <li className="topic-item" key={topic.slug}>
          <Link to={`/${locale}/guides/${guideSlug}/${topic.slug}`} activeClassName="active">
            {topic.title}
          </Link>
          {this.getChildren(topic)}
        </li>
      ))}
    </ul>);
  }
}
export default TopicList;
