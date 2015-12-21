import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { peopleSelector, articlesSelector }
from '../../../../Selectors/list';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';


import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: peopleSelector(state),
    articles: articlesSelector(state),
    selected: state.Publish.list.get('selected')
  });
})

export class ArticlesCardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    articles: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { articles, people, dispatch, selected } = this.props;
    const toggleSelected = (id) => () => dispatch(toggleSelectedAction(id));

    return (
      <div>
        {articles.map((element, index) =>
            <ArticleCard key={index}
                         element={element}
                         toggleSelected={toggleSelected}
                         selected={selected.includes(element.id)}
                         author={people.get(element.person)}/>
        )}
      </div>
    );
  }
}