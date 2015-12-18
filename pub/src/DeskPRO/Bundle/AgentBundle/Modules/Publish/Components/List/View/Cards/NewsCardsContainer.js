import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { connect } from 'react-redux';
import { peopleSelector }
  from '../../../../Selectors/list';

@connect(state => {
  return ({
    people: peopleSelector(state),
    news: state.Publish.list.get('news')
  });
})

export class NewsCardsContainer extends Component {

  static propTypes = {
    people: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired
  };

  render() {
    const { news, people } = this.props;

    return (
      <div>
        {news.map((element, index) =>
            <ArticleCard key={index}
                         element={element}
                         author={people.get(element.person)}/>
        )}
      </div>
    );
  }
}