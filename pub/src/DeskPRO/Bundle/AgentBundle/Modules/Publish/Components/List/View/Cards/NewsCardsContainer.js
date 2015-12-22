import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { connect } from 'react-redux';
import { peopleSelector }
  from '../../../../Selectors/list';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';

@connect(state => {
  return ({
    people: peopleSelector(state),
    news: state.Publish.list.get('news'),
    selected: state.Publish.list.get('selected')
  });
})

export class NewsCardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { news, people, selected, dispatch } = this.props;
    const toggleSelected = (id) => () => dispatch(toggleSelectedAction(id));

    return (
      <div>
        {news.map((element, index) =>
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