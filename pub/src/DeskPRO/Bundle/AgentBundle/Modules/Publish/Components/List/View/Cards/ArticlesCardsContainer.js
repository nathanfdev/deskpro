import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { connect } from 'react-redux';
import { peopleSelector }
  from '../../../../Selectors/list';

@connect(state => {
  return ({
    people: peopleSelector(state),
    articles: state.Publish.list.get('articles')
  });
})

export class ArticlesCardsContainer extends Component {

  static propTypes = {
    people: PropTypes.object.isRequired,
    articles: PropTypes.object.isRequired
  };

  render() {
    const { articles, people } = this.props;

    return (
      <div>
        {articles.map((element, index) =>
            <ArticleCard key={index}
                         element={element}
                         author={people.get(element.person)}/>
        )}
      </div>
    );
  }
}