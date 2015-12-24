import React, {Component, PropTypes} from 'react';
import { ContentCard } from './ContentCard';
import { contentSelector, peopleSelector, articlesSelector, newsSelector, downloadsSelector }
  from '../../../../Selectors/list';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';


import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: peopleSelector(state),
    content: contentSelector(state),
    articles: articlesSelector(state),
    news: newsSelector(state),
    downloads: downloadsSelector(state),
    selected: state.Publish.list.get('selected')
  });
})

export class CardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    people: PropTypes.object.isRequired,
    articles: PropTypes.object,
    news: PropTypes.object,
    downloads: PropTypes.object,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { content, people, dispatch, selected } = this.props;
    const elements = this.props[content];
    const toggleSelected = (id) => () => dispatch(toggleSelectedAction(id));

    return (
      <div>
        {elements.map((element, index) =>
            <ContentCard key={index}
                         element={element}
                         toggleSelected={toggleSelected}
                         selected={selected.includes(element.id)}
                         author={people.get(element.person)}
                         lastRevisionAuthor={people.get(element.last_author_id)}/>
        )}
      </div>
    );
  }
}