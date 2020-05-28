define(['Admin/CustomFields/FieldFormMapper',], function(FieldFormMapper) {
  class CommunityFieldFormMapper extends FieldFormMapper {

    getFormFromModel(fieldModel) {
      const form = super.getFormFromModel(fieldModel);
      if (fieldModel) {
        form.is_global = fieldModel.is_global;
      } else {
        form.is_global = true;
      }

      return form;
    }


    getPostDataFromForm(fieldType, formModel) {
      const postData = super.getPostDataFromForm(fieldType, formModel);
      postData.is_global = formModel.is_global;
      postData.forums = formModel.forums;
      postData.brand = formModel.brand;

      return postData;
    }
  }

  return CommunityFieldFormMapper;
});
