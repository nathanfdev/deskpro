import React, { PropTypes } from 'react';
import Topic from './Topic';

class TopicList extends React.Component {
  static propTypes = {
    topics:    PropTypes.object,
    locale:    PropTypes.string,
    guideSlug: PropTypes.string
  };

  render() {
    const { locale, topics, guideSlug } = this.props;
    return (<ul>
      {Object.values(topics)
        .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
        .map(topic => (
          <Topic key={topic.slug} topic={topic} locale={locale} guideSlug={guideSlug} expandable={false} />
        )
      )}
    </ul>);
  }
}
export default TopicList;
