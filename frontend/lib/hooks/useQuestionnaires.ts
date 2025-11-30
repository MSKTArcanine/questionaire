'use client';

import { getQuestionnaires } from '@/lib/api/questionnaires';
import useSWR from 'swr'; //On evite le useeffect kipu
import { Questionnaire } from '../types';

export function useQuestionnaires() {
  const {
    data,
    error,
    isLoading,
    mutate,
  } = useSWR<Questionnaire[]>(
    '/questionnaires', //La cache key
    () => getQuestionnaires(),  // Fetch sur getqu...
    {
      revalidateOnFocus: false, // fetch pas stp
    },
  );

  return {
    questionnaires: data ?? [],
    isLoading,
    error,
    mutate, // gaffe au Create/Delete pour après et update aussi.
  };
}